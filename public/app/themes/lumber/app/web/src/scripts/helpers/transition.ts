
var numPoints = 10;
var numPaths;
var delayPointsMax = 0.3;
var delayPerPath = 0.25;
var duration = 0.9;
var isOpened = false;
var pointsDelay = [];
var allPoints = [];
var ease = "Power2.easeInOut";
var tl;
var points;
var paths;
var overlay;

function transition() {

    overlay = document.querySelector(".shape-overlays");
    paths = document.querySelectorAll(".shape-overlays__path");

    numPaths = paths.length;

    tl = gsap.timeline({ onUpdate: transitionRender });

    for (var i = 0; i < numPaths; i++) {
        points = [];
        allPoints.push(points);
        for (var j = 0; j < numPoints; j++) {
            points.push(100);
        }
    }

    overlay.addEventListener("click", onClick);
    toggleTransition();

}

function onClick() {
  
  if (!tl.isActive()) {
    isOpened = !isOpened;
    toggleTransition();
  }
}

function toggleTransition() {

  tl.progress(0).clear();
  
  for (var i = 0; i < numPoints; i++) {
    pointsDelay[i] = Math.random() * delayPointsMax;
  }
  
  for (var i = 0; i < numPaths; i++) {
    var points = allPoints[i];
    var pathDelay = delayPerPath * (isOpened ? i : (numPaths - i - 1));
        
    for (var j = 0; j < numPoints; j++) {

        var config = {
            ease: ease,
            duration: duration,
            delay: delay + pathDelay
        };

        config[j] = 0;
        var delay = pointsDelay[j];

        tl.to(points, config);
    }
  }
}

function transitionRender() {
  
  for (var i = 0; i < numPaths; i++) {
    var path = paths[i];
    var points = allPoints[i];
    
    var d = "";
    d += isOpened ? `M 0 0 V ${points[0]} C` : `M 0 ${points[0]} C`;
    
    for (var j = 0; j < numPoints - 1; j++) {
      
      var p = (j + 1) / (numPoints - 1) * 100;
      var cp = p - (1 / (numPoints - 1) * 100) / 2;
      d += ` ${cp} ${points[j]} ${cp} ${points[j+1]} ${p} ${points[j+1]}`;
    }
    
    d += isOpened ? ` V 100 H 0` : ` V 0 H 0`;
    path.setAttribute("d", d)
  }  
}