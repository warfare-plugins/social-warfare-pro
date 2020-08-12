
window.socialWarfare = window.socialWarfare || {};
window.socialWarfare.analytics = window.socialWarfare.analytics || {};

(function(window, $) {

	jQuery(document).ready( function() {
		socialWarfare.analytics.draw_chart();
	});

	socialWarfare.analytics.get_color = function ( name ) {
		var colors = {
			'buffer': '#323b43',
			'facebook': '#1877f2',
			'hacker_news': '#d85623',
			'pinterest': '#e60023',
			'reddit': '#f04b23',
			'tumblr': '#39475d',
			'twitter': '#1da1f2',
			'vk': '#4a76a8',
			'yummly': '#e26426',
		};

		return colors[name];
	}

	socialWarfare.analytics.draw_chart = function() {

		if( jQuery('#analytics_chart').length === 0 ) {
			return;
		}

		var analytics_chart = document.getElementById('analytics_chart').getContext('2d');

		var myLineChart = new Chart(analytics_chart, {
			"type": 'line',
			"data": {
				"datasets":[{
					"label":"Facebook",
					"data":[
						{
							t: '2020.08.01',
							y: 15
						},
						{
							t: '2020.08.02',
							y: 8
						},
						{
							t: '2020.08.03',
							y: 12
						},
						{
							t: '2020.08.05',
							y: 14
						},
					],
					"fill":false,
					"borderColor":socialWarfare.analytics.get_color('facebook'),
					"lineTension":0.3
				}]
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
				}
			}
		});
	}




})(this, jQuery);
