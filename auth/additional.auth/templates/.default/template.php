<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (count($arResult["ERRORS"]) > 0){
foreach ($arResult["ERRORS"] as $key => $error)
if (intval($key) == 0 && $key !== 0)
$arResult["ERRORS"][$key] = str_replace("#FIELD_NAME#", "&quot;".GetMessage("REGISTER_FIELD_".$key)."&quot;", $error);

ShowError(implode("<br />", $arResult["ERRORS"]));
}

?>

<?if (!$arResult['control_string']===true) : ?>
	<form name="form_auth" method="post" target="_top" action="<?=POST_FORM_ACTION_URI?>">

		<div class="log-popup-header"><?=GetMessage("AUTH_TITLE")?></div>
		<hr class="b_line_gray">
		<?ShowMessage($arParams["~AUTH_RESULT"]);?>


		<input type="hidden" name="TYPE" value="SEND_NAME">

		<div id="name_confirmation">
            <div class="login-item">
                <span class="login-item-alignment"></span><span class="login-label"><?=GetMessage("AUTH_NAME")?>*</span>
                <input class="login-inp"  type="text" name="USER_NAME" maxlength="255" />
            </div>
            <div class="login-item">
                <span class="login-item-alignment"></span><span class="login-label"><?=GetMessage("AUTH_SURNAME")?>*</span>
                <input class="login-inp" type="text" name="USER_SURNAME" maxlength="255" />
            </div>
            <div class="login-item">
                <span class="login-item-alignment"></span><span class="login-label"><?=GetMessage("AUTH_SECOND_NAME")?>*</span>
                <input class="login-inp" type="text" name="USER_SECOND_NAME" maxlength="255" />
            </div>
            <div class="login-item">
                <span class="login-item-alignment"></span><span class="login-label"><?=GetMessage("AUTH_NUMBER")?>*</span>
                <input class="login-inp" type="text" name="USER_NUMBER" maxlength="255" />
            </div>

        </div>




        <div class="login-text login-item" id="type_email">
            <?=GetMessage("NAME_DESCRIPTION")?>
            <div class="login-links"><a   href="?send_string=1"><?=GetMessage("TYPE_EMAIL")?></a></div>
        </div>

		<div class="log-popup-footer">
			<button class="login-btn" value="<?=GetMessage("AUTH_GET_CHECK_STRING")?>" onclick="BX.addClass(this, 'wait');"><?=GetMessage("AUTH_GET_CHECK_STRING")?></button>
            <a class="login-link-forgot-pass" href="stream/contact_to_admin.php">Обратиться к администратору</a>
		</div>
	</form>
<?endif?>

<?if ($arResult['control_string']===true) : ?>
    <form name="form_auth" method="post" target="_top" action="<?=POST_FORM_ACTION_URI?>">
        <div class="log-popup-header"><?=GetMessage("AUTH_TITLE2")?></div>
        <hr class="b_line_gray">
        <?ShowMessage($arParams["~AUTH_RESULT"]);?>


        <input type="hidden" name="TYPE" value="SEND_CONTROL_STRING">
        <div id="email_confirmation" >
            <div class="login-item">
                <span class="login-item-alignment"></span><span class="login-label"><?=GetMessage("AUTH_CONTROL_STRING")?></span>
                <input class="login-inp" type="text" name="CONTROL_STRING" maxlength="255" />
            </div>
        </div>

        <div class="login-text login-item"  id="type_name" >
            <?=GetMessage("EMAIL_DESCRIPTION")?>
        </div>

        <div class="log-popup-footer">
            <button class="login-btn" value="<?=GetMessage("AUTH_GET_CHECK_STRING")?>" onclick="BX.addClass(this, 'wait');"><?=GetMessage("AUTH_GET_CHECK_STRING")?></button>
            <a class="login-link-forgot-pass" href="stream/contact_to_admin.php">Обратиться к администратору</a>
        </div>


    </form>
<?endif?>


	<script type="text/javascript">
		// BX.ready(function() {
		// 	BX.focus(document.forms["form_auth"]["USER_LOGIN"]);
		// });
        //
        // function toggleHidden(e) {
        //     e.preventDefault();
        //     document.querySelector('#type_email').classList.toggle('hidden');
        //     document.querySelector('#type_name').classList.toggle('hidden');
        //     nameBlock.classList.toggle('hidden');
        //     emailBlock.classList.toggle('hidden');
        // }
        //
		// let nameLink = document.querySelector('#type_name a');
        // let emailLink = document.querySelector('#type_email a');
        // let emailBlock = document.getElementById('email_confirmation');
        // let nameBlock = document.getElementById('name_confirmation');
        // emailLink.addEventListener('click', toggleHidden);
        // nameLink.addEventListener('click', toggleHidden);

	</script>


