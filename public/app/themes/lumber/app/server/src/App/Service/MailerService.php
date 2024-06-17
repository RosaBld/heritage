<?php

namespace App\Service;

use MailchimpTransactional;
use App\Site;
use Spruce\Utility\Debug;
use Timber\User;

class MailerService {
    protected $site;
    protected $apiKey;
    protected $client = null;
    protected $currentEmail = null;
    protected $currentSubject = null;
    protected $options = [
        "templates" => [ "list" => [ "label" => "Wordpress" ]]
    ];
    protected $emails = [];

    public static function is_iterable( $obj )
    {
        return is_array( $obj ) || ( is_object( $obj ) && ( $obj instanceof \Traversable ) );
    }

    public static function isHTML($string){
        return $string != strip_tags($string) ? true:false;
    }

    public function __construct(Site $site) 
    {
        if( function_exists('acf_add_options_page') ) {
            $this->site = $site;
            $this->addOptionsPage();
            $this->apiKey = get_field("mandrill_api_key", "option");
            if ($this->apiKey != "") {
                $this->currentSubject = null;
                $this->populateFields();
                $this->overrideEmails();
                $this->overrideMailerWordpressFunction();
            }
        }
    }

    protected function populateFields() {
        /* Get all existing templates from Mandrill */
        add_filter('acf/load_field/name=mandrill_template', function ( $field ) {
            $listAttrs = [];
            //
            $label = $this->options["templates"]["list"]["label"];
            if (!is_null($label)) $listAttrs["label"] = $label;
            //
            $list = $this->getClient()->templates->list($listAttrs);
            // Force field if required
            if ($this->apiKey != "") $field["required"] = true;
            //
            $choices = [];
            foreach ($list as $tpl) {
                $choices[$tpl->name] = $tpl->name;
            }
            $field['choices'] = $choices;
            return $field;
        });
    }

    public function addOptionsPage() {
        acf_add_options_sub_page(array(
            'page_title' 	=> "Manage templates from Mandrill",
            'menu_title' 	=> "Templates (emails)",
            'parent_slug' 	=> "options-general.php",
        ));
    }

    protected function getClient() 
    {
        if (is_null($this->client)) {
            $mailchimp = new MailchimpTransactional\ApiClient();
            $mailchimp->setApiKey($this->apiKey);
            $this->client = $mailchimp;
            $email = get_field("mandrill_from_email", "option");
            $this->options["from"] = [
                "name" => get_field("mandrill_from_name", "option"),
                "email" => $email != "" ? $email : "sender@insideapp.be",
            ];
        }

        return $this->client;
    }

    protected function globalAction($globalOptions, $message) 
    {
        $mailchimp = $this->getClient();
        
        $merge_vars = [
            ["name" => "MAIL_TYPE", "content" => "global" ],
            ["name" => "CONTENT", "content" => nl2br($message) ],
        ];

        $m = $mailchimp->templates->render([
            "template_name" => $globalOptions["mandrill_template"],
            "template_content" => [["name" => "content", "content" => ""]],
            "merge_vars" => $merge_vars
        ]);

        return $m->html;
    }

    /* ***
    * 
    * @return HTMLContent
    */
    protected function retrievePasswordAction($retrievePasswordOptions) 
    {
        add_filter( "retrieve_password_title", function($title, $user_login, $user_data ) use ($retrievePasswordOptions) {
            if ($retrievePasswordOptions["subject"] != "") return $retrievePasswordOptions["subject"];
            return $title;
        }, 10, 3);
        add_filter( "retrieve_password_message", function($message, $key, $user_login, $user_data ) use ($retrievePasswordOptions) {
            global $blog_id;
            //
            $url = is_multisite() ? get_blogaddress_by_id( (int) $blog_id ) : home_url('', 'http');
            $rp_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
            
            
            $merge_vars = [
                ["name" => "MAIL_TYPE", "content" => "retrieve-password" ],
                ["name" => "KEY", "content" => $key ],
                ["name" => "USER_LOGIN", "content" => $user_login ],
                ["name" => "ACTION_LINK", "content" => $rp_link ],
                // ["name" => "FIRST_NAME", "content" => $tUser->first_name ],
                // ["name" => "LAST_NAME", "content" => $tUser->last_name ],
            ];
            //
            $mailchimp = $this->getClient();
            // 
            try {
                foreach ($user_data->data as $key => $data) {
                    $merge_vars[] = [ "name" => strtoupper($key), "content" => $data ];
                }
            } catch (\Exception $e) {}
            
            $m = $mailchimp->templates->render([
                "template_name" => $retrievePasswordOptions["mandrill_template"],
                "template_content" => [["name" => "content", "content" => ""]],
                "merge_vars" => $merge_vars
            ]);

            if(isset($m->html)) {
                $message = $m->html;
            }
            
            return $message;
        }, 10, 4);
    }
    
    /* ***
    * 
    * @return HTMLContent
    */
    protected function passwordChangeEmailAction($options) 
    {
        add_filter( "password_change_email", function($pass_change_email, $user, $user_data ) use ($options) {
            /* Get the right subject for the e-mail */
            if ($options["subject"] != "")
                $this->currentSubject = $options["subject"];

            $tUser = new User($user->ID);
            $merge_vars = [
                ["name" => "MAIL_TYPE", "content" => "password-change-email" ],
                ["name" => "USER", "content" => $user ],
                ["name" => "FIRST_NAME", "content" => $tUser->first_name ],
                ["name" => "LAST_NAME", "content" => $tUser->last_name ],
            ];
            //
            $mailchimp = $this->getClient();
            // 
            try {
                foreach ($user_data->data as $key => $data) {
                    $merge_vars[] = [ "name" => strtoupper($key), "content" => $data ];
                }
            } catch (\Exception $e) {}
            
            $m = $mailchimp->templates->render([
                "template_name" => $options["mandrill_template"],
                "template_content" => [["name" => "content", "content" => ""]],
                "merge_vars" => $merge_vars
            ]);

            if(isset($m->html)) {
                $message = $m->html;
            }
            
            return $message;
        }, 10, 3);
    }
    
    /* ***
    * E-mail sent to new user
    * @return HTMLContent
    */
    protected function newUserNotificationEmailAction($options) 
    {
        add_filter( "wp_new_user_notification_email", function($wp_new_user_notification_email, $user, $blogname ) use ($options) {

            /* Get the right subject for the e-mail */
            if ($options["subject"] != "")
                $this->currentSubject = $options["subject"];
            
            $tUser = new User($user->ID);
            $merge_vars = [
                ["name" => "MAIL_TYPE", "content" => "new-user-notification-email" ],
                ["name" => "USER", "content" => $user ],
                ["name" => "FIRST_NAME", "content" => $tUser->first_name ],
                ["name" => "LAST_NAME", "content" => $tUser->last_name ],
            ];
            //

            $mailchimp = $this->getClient();
            // 
            try {
                foreach ($user->data as $key => $data) {
                    $merge_vars[] = [ "name" => strtoupper($key), "content" => $data ];
                }
            } catch (\Exception $e) {}
            
            try {
                foreach ($user->data as $key => $data) {
                    $merge_vars[] = [ "name" => strtoupper($key), "content" => $data ];
                }
            } catch (\Exception $e) {}
            
            $m = $mailchimp->templates->render([
                "template_name" => $options["mandrill_template"],
                "template_content" => [["name" => "content", "content" => ""]],
                "merge_vars" => $merge_vars
            ]);

            if(isset($m->html)) {
                $message = $m->html;
            }
            
            return $message;
        }, 10, 3);
    }

    protected function overrideEmails() 
    {

        $retrievePasswordOptions = get_field("mandrill_retrieve_password", "option");
        if (
            !is_null($retrievePasswordOptions) 
            && isset($retrievePasswordOptions["is_active"]) 
            && $retrievePasswordOptions["is_active"] == true
        ) {
            $this->retrievePasswordAction($retrievePasswordOptions);
        }
        
        $password_change_email = get_field("mandrill_password_change_email", "option");
        if (
            !is_null($password_change_email) 
            && isset($password_change_email["is_active"]) 
            && $password_change_email["is_active"] == true
        ) {
            $this->passwordChangeEmailAction($password_change_email);
        }
        
        $wp_new_user_notification_email = get_field("mandrill_wp_new_user_notification_email", "option");
        if (
            !is_null($wp_new_user_notification_email) 
            && isset($wp_new_user_notification_email["is_active"]) 
            && $wp_new_user_notification_email["is_active"] == true
        ) {
            $this->newUserNotificationEmailAction($wp_new_user_notification_email);
        }
    }

    protected function overrideMailerWordpressFunction () 
    {
        add_filter( 'pre_wp_mail', function($null, $atts) {
            
            $message = $atts['message'];
            $to = $atts['to'];
            $subject = is_null($this->currentSubject) ? $atts['subject'] : $this->currentSubject;
            $headers = explode("\n", $atts['headers']);
            $attachments = $atts['attachments'];
            //
            $client = $this->getClient();

            $emailMessage = [
                "from_email" => $this->options["from"]["email"],
                "from_name" => $this->options["from"]["name"],
                // "headers" => $headers,
                "subject" => $subject,
                "text" => \strip_tags($message),
                "to" => [
                    [
                        "email" => $atts['to'],
                        "type" => "to",
                    ]
                ],
            ];

            // Only send as HTML
            if (MailerService::isHTML($message)) {
                $emailMessage["html"] = $message;
            } else {
                $globalOptions = get_field("mandrill_global", "option");
                if ($globalOptions["is_active"] == true) {
                    $emailMessage["html"] = $this->globalAction($globalOptions, $message);
                }
            }
            
            $response = $client->messages->send([
                "message" => $emailMessage
            ]);
            if (isset($response[0]) && isset($response[0]->status) && in_array($response[0]->status, ["sent"])) {
                return true;
            } else {
                return false;
            }
            
            // By sending boolean (true => E-mail sent, false => Failed, null => Error, Array => override)
            return true;
        }, 10, 2);
    }

}
