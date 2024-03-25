/**
 * Wind Turbine (Vase mode) by jotalamp 
 * on Thingiverse: https://www.thingiverse.com/thing:2241699
 */

tol = 0.4; // Toleranssi

t = 0.30;

d1 = 80.00;
d2 = 22.00 + tol;
d3 = 20.00 + tol;

r1 = d1/2;
r2 = d2/2;
r3 = d3/2;

z1 = 170.00;

e = 0.1;

// Global resolution
$fs = 0.1;  // Don't generate smaller facets than 0.1 mm
$fa = 5;    // Don't generate larger angles than 5 degrees

// Main geometry
module the_turbine() {

  linear_extrude(height=z1, twist=180) {
    difference() {
      union() {
        wings(d=d1);
        stick(d=d3);
      }
      // hole(d=d3);
    }
  }
  // stick(d=d3 / 2);
}


module wings(d) {
  D=d/2;
  difference() {
    translate([ 0.98*D,0,0]) circle(d=d);
    translate([ D,0.2*D,0]) circle(d=d);
  }
  difference() {
    translate([-0.98*D,0,0]) circle(d=d);
    translate([-D,-0.2*D,0]) circle(d=d);
  }
}

module stick(d) {
  circle(d=d);
}

module hole(d) {
  circle(d=d);
  translate([r3,0]) square([r2,3],true);
}

/********************************
 * The main 
 ********************************/
if (false) {
  the_turbine();
} else {
  echo ("-----------------------------------------------");
  echo(">>> Nothing rendered, see the bottom of the code");
  echo ("-----------------------------------------------");
}


