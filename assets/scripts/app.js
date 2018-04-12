/**
 * Citation: https://stackoverflow.com/questions/36949343/chart-js-dynamic-changing-of-chart-type-line-to-bar-as-example
 * @param type
 */
function switchType(type) {
  var ctx = document.getElementById("chart");

  // Remove the old chart
  if (chart) {
    chart.destroy();
  }

  // Modify the object using jQuery
  var tempConfig = jQuery.extend(true, {}, config);
  tempConfig.type = type;
  chart = new Chart(ctx, tempConfig);
}
