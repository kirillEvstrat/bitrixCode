<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


$link_skript_folber = $arResult['COMPONENTS_FOLBER_PATH']. 'components/'.$arResult['COMPONENT_NAME_URL']
		.'/templates/'.$arResult['TEMPLATE_NAME'];

$APPLICATION->AddHeadScript($link_skript_folber."/bililiteRange.js");
$APPLICATION->AddHeadScript($link_skript_folber."/jquery.sendkeys.js");


CJSCore::Init(array('jquery'));

?>     
<div class="crm-view-table-total-inner">
	<table>
		<tbody>
			<tr>
				<td>
					<div>
						<span>Tax Rate</span>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<div style="vertical-align:central">
                        <select onchange="calculateTax(this)" name="" class="crm-item-table-select" id="region-selector">
                            <?
                            foreach ($arResult['departments'] as $i => $department) {
                                if($department['ID'] == $arResult['TAX_ID']){
                            ?>
                                    <option value="<?=$department['RATE']?>" data-id="<?=$department['ID']?>" selected><?=$department['NAME']?></option>

                            <?
                                    }
                                else {
                                    ?>
                                    <option value="<?= $department['RATE'] ?>"
                                            data-id="<?= $department['ID'] ?>"><?= $department['NAME'] ?></option>
                                    <?
                                }
                            }
                            ?>
                        </select>
<!--						<input --><?//=$arResult['checked_multi']?><!-- onclick="runDiscount(this);" id="multi_disc" type="checkbox"><span>Multi-Office Discount</span>-->
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>


<script>


    let serviceUrlRegion = '<?=$arResult['AJAX_LINK']?>';
    let entity_idRegion = '<?=$arParams['ENTITY_ID']?>';
    let regionId = '<?=$arResult['TAX_ID']?>';
    let taxValue = '<?=$arResult['TAX_VALUE']?>';

    function calculateTax(e) {
        //console.log("test");
        let flag = false;
        let selectedIn = e.selectedIndex;
        let selectedOption = e[selectedIn];
        regionId = selectedOption.getAttribute('data-id');


        let value = e.value;

        if(value==="0.00"){
            value = "0";
        }

        let productId = 0;
        let productTaxSelector = document.getElementById('deal_product_editor_product_row_'+productId+'_TAX_RATE');

        while(productTaxSelector){
            let options = productTaxSelector.getElementsByTagName("option");
            for(let i=0; i<options.length; i++){
                if( options[i].value!==value){
                    options[i].setAttribute('disabled', "disabled");

                }
                else{
                    options[i].removeAttribute('disabled');
                }


            }
            //if(productTaxSelector.value!=="0"){
                productTaxSelector.value = value;
                let event = new Event("change", {bubbles: true, cancelable: false});
                event.value = value;
                productTaxSelector.dispatchEvent(event);
           // }

            productId++;
            productTaxSelector = document.getElementById('deal_product_editor_product_row_'+productId+'_TAX_RATE')
        }

    }

    if(taxValue!==''){
        document.addEventListener('DOMContentLoaded', function() {
            let value = taxValue;
            let productId = 0;
            let productTaxSelector = document.getElementById('deal_product_editor_product_row_' + productId + '_TAX_RATE');

            while (productTaxSelector) {
                let options = productTaxSelector.getElementsByTagName("option");
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value !== "0" && options[i].value !== value) {
                        options[i].setAttribute('disabled', "disabled");
                    }
                }
                productId++;
                productTaxSelector = document.getElementById('deal_product_editor_product_row_' + productId + '_TAX_RATE')
            }
        });

    }

    BX.ready(function() {
        BX.addCustomEvent('productAdd', BX.delegate(function (e) {
            console.log("--");

            let taxValue = document.getElementById('region-selector').value;

            if(taxValue==="0.00"){
                taxValue = "0";
            }

            console.log(taxValue);
            let container = e.product._container;
            let productTaxSelector = container.querySelector('.crm-item-tax select');
            let options = productTaxSelector.querySelectorAll("option");
            console.dir(options);
            for (let i = 0; i < options.length; i++) {
                if (options[i].value !== taxValue) {
                    options[i].setAttribute('disabled', "disabled");

                } else {
                    options[i].removeAttribute('disabled');
                }


            }
            //if(productTaxSelector.value!=="0"){
            productTaxSelector.value = taxValue;
            let event = new Event("change", {bubbles: true, cancelable: false});
            event.value = taxValue;
            productTaxSelector.dispatchEvent(event);

            console.log(container);
            console.log("___")
        }));
    });





</script>