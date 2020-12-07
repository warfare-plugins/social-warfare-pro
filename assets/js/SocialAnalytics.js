/**
 * The SocialAnalytics class will control all of the javascript on the Socal
 * Analytics submenu page in the admin area. This will instantiate all of our
 * charts as well.
 *
 * @since  4.2.0 | 25 AUG 2020 | Created
 *
 */
class SocialAnalytics {

	/**
	 * The class is instantiated on the jQuery(document).ready() event. At this
	 * time this constructor will run and begin rendering the charts, registering
	 * click handlers, and anything else that needs done.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	constructor() {

		// Render any charts that exist on the page.
		this.draw_charts();

		// Handle any clicks on our timeframe buttons.
		jQuery(document).on('click', '.sw-chart-timeframe', this.update_timeframe.bind(this) );
	}


	/**
	 * The draw_charts() method will scan the page for any canvas elements with
	 * the class name of swp_analytics_chart. These elements will have data
	 * attributes that will allow us to link that canvas with a set of JS data
	 * that is being output by the server. That data will contain the pieces
	 * needed to render out a chart. This method will tie all of that together.
	 *
	 * @since  4.2.0 | 28 AUG 2020
	 * @param  void
	 * @return void
	 *
	 */
	draw_charts() {

		// So we can continue to access this outside of this scope
		var self     = this;

		// An associative array of chart.js chart objects.
		self.charts  = {};

		// Find the canvases on the page. If there aren't any, just bail out.
		var canvases = jQuery('.swp_analytics_chart');
		if( canvases.length === 0 ) {
			return;
		}

		// Loop through each of the canvases that we found above to render them.
		canvases.each( function() {

			// Fetch the data attributes from the canvas element.
			var key         = jQuery(this).data('key');
			var type        = jQuery(this).data('type');

			// Fetch the 2d context of the canvas element so that we can work on it.
			var canvas      = this.getContext('2d');

			// Skip this chart if it does not have valid data available. If this
			// fails to happen, it ends up throwing JS errors later that break
			// the page.

		//	if( 'undefined' === typeof this.chart_data[key].datasets || null === this.chart_data[key].datasets ) {

				// This is the $(selector).each() equivelent of "continue" and
				// is used to move it onto the next iteration of the loop.
		//		return true;
		//	}
		//
			// Filter the data so it displays the default timeframe.
			self.filter_data(key);

			// Instantiate a new chart.js chart object.
			self.charts[key] = new Chart(canvas, {

				// The type of chart being rendered (line or bar).
				type: type,

				// The data that will be used to populate the plots.
				data: {
					datasets: self.chart_data[key].datasets
				},

				// Stuff that controls the look, feel and behavior of the chart.
				options: {
					maintainAspectRatio: false,
					scales: {
						xAxes: [{
							type: 'time',
							time: {
								unit: 'day',
								stepSize: self.chart_data[key].stepSize,
							},
							offset: self.chart_data[key].offset,
							ticks: {
								min: 0
							}
						}],
						yAxes: [{
							ticks: {
								callback: function(value, index, values) {
									return self.number_format(value);
								},
								beginAtZero: (self.chart_data[key].type === 'bar' ? true : false ),
								userCallback: function(label, index, labels) {
									if( Math.floor(label) === label ) {
										return label;
									}
								}
							}
						}]
					},
					legend: {
						labels: {
							usePointStyle: true,
							fontSize     : 12,
							padding      : 20
						},

						// This makes the legend have a pointer when hovered.
						onHover: function(e) {
							e.target.style.cursor = 'pointer';
						}
					},

					// This makes the legend have a pointer when hovered.
					hover: {
						onHover: function(e) {
							var point = this.getElementAtEvent(e);
							if (point.length) {
									 e.target.style.cursor = 'pointer';
							} else {
								e.target.style.cursor = 'default';
							}
						}
					},
					tooltips: {
						callbacks: {
							title: function( tooltipItem, data ) {
								var label = new Date( tooltipItem[0].label + ' 0:0:0 GMT' );
								label     = label.toLocaleDateString("en-EN", {month: "short", day: "2-digit", year: "numeric", timeZone: 'UTC'});
								return label;
							},
							label: function( tooltipItem, data ) {
								return data.datasets[tooltipItem.datasetIndex].label + ': ' + self.number_format(tooltipItem.value) + ' shares';
							}
						},
						cornerRadius : 3,
						titleFontSize: 15,
						bodyFontSize : 14,
						xPadding     : 15,
						yPadding     : 15,
					}
				}
			});
		});
	}


	/**
	 * The filter_data() method will filter out the data for the chart. Most
	 * charts will be provided with enough data to go back through the historical
	 * record indefinitely. However, we only want to show enough data to
	 * represent the currently selected timeframe such as 1 week, 1 month, etc.
	 * So prior to the chart being rendered or updated, the data array for the
	 * chart will be sliced based on the currently selected timeframe.
	 *
	 * @since  4.2.0 | 28 AUG 2020
	 * @param  string chart_key The unique key corresponding to a chart on the page.
	 * @return void
	 *
	 */
	filter_data(chart_key) {

		// Allows us to access 'this' inside of anonymous functions.
		var self = this;

		// Make a deep, value-based, not-by-reference copy of the chart data.
		this.chart_data = JSON.parse(JSON.stringify(chart_data));

		// Loop through each set of data for the chart.
		var i = 0;

		this.chart_data[chart_key].datasets.forEach( function( dataset ) {

			// Calculate the start and end indexes of the array.
			var start = dataset.data.length - chart_data[chart_key].range;
			var end   = dataset.data.length + 1;

			// Cut the array in half using the start and end points.
			dataset   = dataset.data.slice(start,end);

			// If it's not undefined, we are updating an existing chart.
			if( 'undefined' !== typeof self.charts[chart_key] ) {
				self.charts[chart_key].data.datasets[i].data = dataset;
			}
			i++;
		})
	}


	/**
	 * The update_timeframe() method is used to change the timeframe on an
	 * existing chart. This will allow the user to toggle between 7 days, 30 days,
	 * etc. This will update the styles of the buttons as well as update the
	 * visual data being displayed on the chart.
	 *
	 * Note: This is a callback function for a click handler.
	 *
	 * @since  4.2.0 | 28 AUG 2020 | Created
	 * @param  object event The event object.
	 * @return void
	 *
	 */
	update_timeframe( event ) {

		// Use the data attributes on the button to fetch our variables.
		let range     = jQuery(event.target).data('range');
		let chart_key = jQuery(event.target).data('chart');

		// Update the data being displayed on the chart.
		socialWarfare.SocialAnalytics.update_chart(chart_key, range );

		// Change which timeframe button appears to be active on the screen.
		jQuery(event.target).parent().find('.sw-chart-timeframe').removeClass('active');
		jQuery(event.target).addClass('active');
	}


	/**
	 * The update_chart() method will take the newly selected date range,
	 * filter the date down to only that range, and then rerender the chart
	 * based on the new data.
	 *
	 * @since  4.2.0 | 29 AUG 2020 | Created
	 * @param  string  chart_key The unique id corresponding to a chart on the page.
	 * @param  integer range     The number of days to display on the chart.
	 * @return void
	 *
	 */
	update_chart( chart_key, range ) {
		chart_data[chart_key].range = range;
		this.filter_data(chart_key);
		this.charts[chart_key].render();
		this.charts[chart_key].update();
	}


	/**
	 * The number_format() mimics PHP's number format in regard to adding commas
	 * to separate thousands.
	 *
	 * @since  4.2.0 | 28 AUG 2020 | Created
	 * @param  integer number The number to be formatted.
	 * @return string  number The formatted number.
	 *
	 */
	number_format(number) {
	    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
}

// Kick everything off once the page is loaded.
jQuery(document).ready( function() {
	window.socialWarfare = window.socialWarfare || {};
	socialWarfare.SocialAnalytics = new SocialAnalytics;
});
