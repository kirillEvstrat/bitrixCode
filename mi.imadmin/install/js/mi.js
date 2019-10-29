/*;(function() {
	
	function Page () {	
	
		this.getAction = _getAction(window.location.pathname);

		function _getAction(path){
			var page = false;		

			if(~path.indexOf("crm/lead/edit")) {
				page = "lead_edit";
			} else if(~path.indexOf("crm/lead/show")){
				page = "lead_show";
			}		
			return page;
		}		
	}
	
	function CrmCard () {
		var inserter = false;
		var type = false;
		
		this.insertElem = _insertElem;
		this.setType = _setType;
		this.hiddenBlockElem = _hiddenBlockElem;
		
						
		function _insertElem(elem, parentElemId, hide) {
			hide = hide || 'N';
			
			if(typeof this.inserter === "object") {
				this.inserter.insert(elem, parentElemId, hide);				
			}
		}
		
		function _setType(page) {
			if(page === "lead_edit" || page === "deal_edit") {
				this.type = "edit";
				this.inserter = new UfInsertorEditType();
				return this.inserter;
			}
		}
		
		function _hiddenBlockElem (arBlockId){
			for (var i = 0; i < arBlockId.length; i++ ) {
				if(document.getElementById(arBlockId[i])){				
					document.getElementById(arBlockId[i]).style.display = 'none';			
				}
			}
		}
			
	}
	
	
	function UfInsertorEditType (){
				
		this.insert = _insert;		
		
		function _insert(element, fieldId, hide) {			
			var id = fieldId.toLowerCase() + "_wrap";		
			
			var newDiv = document.createElement('div');
			newDiv.id = 'de_' + fieldId;
			newDiv.appendChild(element);
			
			if(document.querySelector('#' + id + " .bx-crm-edit-user-field")){
				var paerntElem = document.querySelector('#' + id + " .bx-crm-edit-user-field");
				paerntElem.appendChild(newDiv);
				if(hide === 'Y') {
					paerntElem.querySelector(".fields").style.display = 'none';					
				}
			}
		}
	}
	
	
	function UfInsertorShowType (){
		;
	}
	
	
	
	var _DE = {};
	_DE.Page = new Page();
	_DE.CrmCard = new CrmCard();
	
	
	window.DE = _DE;
}());


/*
//DE.Page(window.location.pathname);
//var currentPage = new Page(window.location.pathname);
BX.ready(function(){ 
	var actionPage = DE.Page.getAction;
	
	
	
	if(actionPage) {
		switch (actionPage) {
			case "lead_edit":			
				
				var arBlockEditHidden = [
					
					'section_fenjzcrp_contents',
					'main_UF_CRM_1515657758',
					'uf_crm_1515664340_wrap',
					'uf_crm_1515329602_wrap',
					'uf_crm_1515664324_wrap',
					
				];			
				
				DE.CrmCard.hiddenBlockElem(arBlockEditHidden);				
				break;
			
			case "lead_show":
				
				var arBlockShowHidden = [
				
					//'uf_crm_1515657758_wrap',
					'main_UF_CRM_1515657758',
					
					'uf_crm_1515664340_wrap', // id файла
					'section_fenjzcrp_contents', // стандартные контакты в лиде
					'uf_crm_1515329602_wrap',
					'uf_crm_1515664324_wrap',
					
				];
				DE.CrmCard.hiddenBlockElem(arBlockShowHidden);
				break;
		}
		
		
	}
});*/
