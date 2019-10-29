BX.ready(function() {
	BX.addCustomEvent('onCrmEntityUpdate', BX.delegate(function() {
		let region = document.getElementById('region-selector');

		let data = {
		'regionId' : regionId,'action' : 'set_region_id', 'entity_id' : entity_idRegion, 'bitrix_sessid' : BX.bitrix_sessid()
		};
		console.dir(data);

		
		BX.ajax({
			data: data,
					
			method: 'POST',
			dataType: 'json',
			url : serviceUrlRegion,
			
			onsuccess: function (data) {
				console.log(data);
			},
			onfailure: function (data) {
				console.log(data);
			}
		});			
	}));
	
	BX.addCustomEvent('onEntityDetailsTabShow', BX.delegate(function() {
		lockChange();	
	}));


	
});