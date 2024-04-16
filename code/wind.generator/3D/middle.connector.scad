include <./spiraltest04.scad>
use <../../../../3DPrinting/OpenSCAD/mechanical.parts.scad>

/**
 * middle connector, when 2 spirals are superposed.
 * One spiral on top, 
 * One spiral at the bottom
 */
 
STEPPER_AXIS_DIAM = 5; 
 
SPIRAL_D3 = d3;     // from spiraltest04.scad
SPIRAL_HEIGHT = z1; // from spiraltest04.scad

BASE_HEIGHT = 15;
SLACK = 0.75; // 0.25;

difference() {
  translate([0, 0, 0]) { // - (BASE_HEIGHT / 2) + 5]) {
    rotate([0, 0, 0]) {
      cylinder(h=BASE_HEIGHT, r=(SPIRAL_D3 / 2) * 1.5, center=true, $fn=100);
    }
  }
  // To leave the footprint, on top
  translate([0, 0, SLACK]) {
    the_turbine();
    // 2 other ones, to get some slack...
    if (true) {
      translate([SLACK, 0, 0]) {
        the_turbine();
      }
      translate([-SLACK, 0, 0]) {
        the_turbine();
      }
    }
  }
  
  // To leave the footprint, at the bottom
  translate([0, 0, -SPIRAL_HEIGHT - SLACK]) {
    the_turbine();
    // 2 other ones, to get some slack...
    if (true) {
      translate([SLACK, 0, 0]) {
        the_turbine();
      }
      translate([-SLACK, 0, 0]) {
        the_turbine();
      }
    }
  }
}
