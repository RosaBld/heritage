import gsap from "gsap";

export default class TransitionEffect {
    
    public isOpened = false;
    public numPoints = 5;
    public numPaths;
    public delayPointsMax = 0.3;
    public delayPerPath = 0.25;
    public duration = 0.9;
    public pointsDelay = [];
    public allPoints = [];
    public ease = "Power2.easeInOut";
    public points;
    public paths;
    public overlay;
    public tl;
    public delay;
    public screen;

    public constructor() {
        this.screen = document.querySelector(".transition-screen");
        this.overlay = document.querySelector(".shape-overlays");
        this.paths = document.querySelectorAll(".shape-overlays__path");

        this.numPaths = this.paths.length;
        let that = this;
        this.tl = gsap.timeline({ onUpdate: () => {
            that.render();
        }});

        for (var i = 0; i < this.numPaths; i++) {
            this.points = [];
            this.allPoints.push(this.points);
            for (var j = 0; j < this.numPoints; j++) {
                this.points.push(100);
            }
        }

        // this.overlay.addEventListener("click", () => {
        //     that.toggle();
        // });
    }

    public isOpen() {
        this.isOpened = true;
    }
    
    public isClose() {
        this.isOpened = false;
    }

    public toggle() {
        if (this.tl.isActive()) return false;
        this.screen.classList.add("active");
        this.isOpened = !this.isOpened;
        this.tl.progress(0).clear();
  
        for (var i = 0; i < this.numPoints; i++) {
            this.pointsDelay[i] = Math.random() * this.delayPointsMax;
        }
        
        for (var i = 0; i < this.numPaths; i++) {
            this.points = this.allPoints[i];
            var pathDelay = this.delayPerPath * (this.isOpened ? i : (this.numPaths - i - 1));
              
            for (var j = 0; j < this.numPoints; j++) {

                var config = {
                    ease: this.ease,
                    duration: this.duration
                };
                config[j] = 0;
                var delay = this.pointsDelay[j];
                this.tl.to(this.points, config, delay + pathDelay);
            }
        }
        return this.tl;
    }

    public removeScreen() {
        if (!this.isOpened) {
            this.screen.classList.remove("active");
        }
    }

    public render () {
        for (var i = 0; i < this.numPaths; i++) {
            var path = this.paths[i];
            this.points = this.allPoints[i];
            
            var d = "";
            d += this.isOpened ? `M 0 0 V ${this.points[0]} C` : `M 0 ${this.points[0]} C`;
            
            for (var j = 0; j < this.numPoints - 1; j++) {
              
              var p = (j + 1) / (this.numPoints - 1) * 100;
              var cp = p - (1 / (this.numPoints - 1) * 100) / 2;
              d += ` ${cp} ${this.points[j]} ${cp} ${this.points[j+1]} ${p} ${this.points[j+1]}`;
            }
            
            d += this.isOpened ? ` V 100 H 0` : ` V 0 H 0`;
            path.setAttribute("d", d)
        }
    }
};
