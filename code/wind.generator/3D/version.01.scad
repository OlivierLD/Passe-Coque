include <./spiraltest04.scad>
use <../../../../3DPrinting/OpenSCAD/mechanical.parts.scad>

/**
 * Top and bottom parts, axis.
 */
 
STEPPER_AXIS_DIAM = 5; 
 
SPIRAL_D3 = d3; // from piraltest04.scad
BASE_HEIGHT = 10;

BOTTOM = 1;
TOP = 2;

TOP_OR_BOTTOM = BOTTOM;

difference() {
  translate([0, 0, 0]) { // - (BASE_HEIGHT / 2) + 5]) {
    rotate([0, 0, 0]) {
      cylinder(h=BASE_HEIGHT, r=(SPIRAL_D3 / 2) * 1.5, center=true, $fn=100);
    }
  }
  // To leave the footprint in the base
  the_turbine();
  // TODO A socket for a ball bearing (top one)
  
  if (TOP_OR_BOTTOM == BOTTOM) {
    // TODO The axis to connect to the stepper
    screwLen = 20;
    translate([0, 0, -screwLen - 3.5]) {
      rotate([0, 0, 0]) {
			  metalScrewHB(STEPPER_AXIS_DIAM, screwLen); //, 10);
      }
    }
  }
  if (TOP_OR_BOTTOM == TOP) {
    echo ("Ca vient");
  }
}
