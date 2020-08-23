
class SocialAnalytics {

	constructor() {
		this.draw_charts();
	}

	draw_charts() {
		var self = this;
		var canvases = jQuery('.swp_analytics_chart');
		if( canvases.length === 0 ) {
			return;
		}


		canvases.each( function() {
			var key = jQuery(this).data('key');
			var type = jQuery(this).data('type');
			var canvas = this.getContext('2d');
			var new_chart = new Chart(canvas, {
				"type": type,
				"data": {
					"datasets": chart_data[key].datasets
				},
				"options": {
					'maintainAspectRatio': false,
					scales: {
						xAxes: [{
							type: 'time',
							time: {
								unit: 'day',
								stepSize: chart_data[key].stepSize
							},
							ticks: {
							}
						}],
						yAxes: [{
							ticks: {
								callback: function(value, index, values) {
									return self.number_format(value);
								}
							}
						}]
					},
					tooltips: {
						callbacks: {
							title: function( tooltipItem, data ) {
								var label = new Date( tooltipItem[0].label );
								label     = label.toLocaleDateString("en-EN", {month: "short", day: "2-digit", year: "numeric"});
								return label;
							}
						}
					}
				}
			});


		});

	}

	number_format(x) {
	    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
}



jQuery(document).ready( function() {
	window.socialWarfare = window.socialWarfare || {};
	socialWarfare.SocialAnalytics = new SocialAnalytics;
});
