
class SocialAnalytics {

	constructor() {
		this.draw_charts();
	}

	draw_charts() {

		var canvases = jQuery('.swp_analytics_chart');
		if( canvases.length === 0 ) {
			return;
		}


		canvases.each( function() {
			var key = jQuery(this).data('key');
			var canvas = this.getContext('2d');
			console.log(chart_data[key]);
			var new_chart = new Chart(canvas, {
				"type": 'line',
				"data": {
					"datasets": chart_data[key]
				},
				"options": {
					'maintainAspectRatio': false,
					scales: {
						xAxes: [{
							type: 'time',
							time: {
								unit: 'day'
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

	get_color( name ) {
		var colors = {
			'buffer'        : '#323b43',
			'facebook'      : '#1877f2',
			'hacker_news'   : '#d85623',
			'pinterest'     : '#e60023',
			'reddit'        : '#f04b23',
			'tumblr'        : '#39475d',
			'twitter'       : '#1da1f2',
			'vk'            : '#4a76a8',
			'yummly'        : '#e26426',
			'social_warfare': '#ee464f'
		};

		return colors[name];
	}
}



jQuery(document).ready( function() {
	window.socialWarfare = window.socialWarfare || {};
	socialWarfare.SocialAnalytics = new SocialAnalytics;
});
