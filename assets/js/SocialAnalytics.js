
class SocialAnalytics {

	constructor() {
		this.draw_charts();
		jQuery(document).on('click', '.sw-chart-timeframe', this.update_timeframe.bind(this) );
	}

	draw_charts() {
		var self     = this;
		self.charts  = {};
		var canvases = jQuery('.swp_analytics_chart');
		if( canvases.length === 0 ) {
			return;
		}


		canvases.each( function() {
			var key         = jQuery(this).data('key');
			var type        = jQuery(this).data('type');
			var canvas      = this.getContext('2d');

			self.filter_data(key);
			self.charts[key] = new Chart(canvas, {
				"type": type,
				"data": {
					"datasets": self.chart_data[key].datasets
				},
				"options": {
					'maintainAspectRatio': false,
					scales: {
						xAxes: [{
							type: 'time',
							time: {
								unit: 'day',
								stepSize: self.chart_data[key].stepSize,
							},
							offset: self.chart_data[key].offset,
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
								var label = new Date( tooltipItem[0].label + ' 0:0:0 GMT' );
								label     = label.toLocaleDateString("en-EN", {month: "short", day: "2-digit", year: "numeric", timeZone: 'UTC'});
								return label;
							},
							label: function( tooltipItem, data ) {
								return data.datasets[tooltipItem.datasetIndex].label + ': ' + self.number_format(tooltipItem.value);
							}
						}
					}
				}
			});
		});
	}

	filter_data(chart_key) {
		var self = this;

		this.chart_data = JSON.parse(JSON.stringify(chart_data));
		var i = 0;
		this.chart_data[chart_key].datasets.forEach( function( dataset ) {
			var start = dataset.data.length - chart_data[chart_key].range;
			var end   = dataset.data.length + 1;
			dataset   = dataset.data.slice(start,end);

			if( 'undefined' !== typeof self.charts[chart_key] ) {
				self.charts[chart_key].data.datasets[i].data = dataset;
			}
			i++;
		})
	}

	number_format(x) {
	    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}

	update_timeframe( event ) {
		let range = jQuery(event.target).data('range');
		let chart_key = jQuery(event.target).data('chart');

		socialWarfare.SocialAnalytics.update_chart(chart_key, range );
		jQuery(event.target).parent().find('.sw-chart-timeframe').removeClass('active');
		jQuery(event.target).addClass('active');
	}

	update_chart( chart_key, range ) {
		chart_data[chart_key].range = range;
		this.filter_data(chart_key);
		this.charts[chart_key].render();
		this.charts[chart_key].update();
	}
}



jQuery(document).ready( function() {
	window.socialWarfare = window.socialWarfare || {};
	socialWarfare.SocialAnalytics = new SocialAnalytics;

	// This is how to trigger a new time range once we add buttons.
	setTimeout( function() {
	//	socialWarfare.SocialAnalytics.update_chart(chart_key, 1);
	}, 3000)
});
