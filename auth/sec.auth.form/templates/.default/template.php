<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<?
ShowMessage($arParams["~AUTH_RESULT"]);
ShowMessage($arResult['ERROR_MESSAGE']);


if (count($arResult["ERRORS"]) > 0){
    foreach ($arResult["ERRORS"] as $key => $error)
        if (intval($key) == 0 && $key !== 0)
            $arResult["ERRORS"][$key] = str_replace("#FIELD_NAME#", "&quot;".GetMessage("REGISTER_FIELD_".$key)."&quot;", $error);

    ShowError(implode("<br />", $arResult["ERRORS"]));
}

?>




<?if (!$arResult['repeat']===true && $arResult['step']===1):?>
<div class="bx-auth">
    <div class="bx-auth-title"><?=GetMessage("AUTH_PLEASE_AUTH")?></div>

    <form name="form_auth" method="post" target="_top" class="confirm-form" action="<?=POST_FORM_ACTION_URI?>" >
        <input type="hidden" name="step" value="1" />
        <input type="hidden" name="USER_ID" value="<?=$arResult['ID']?>" />
        <input type="hidden" name="USER_LOGIN" value="<?=$arResult['LOGIN']?>" />
        <table class="bx-auth-table">
            <tr>
                <td class="bx-auth-label">Фамилия</td>
                <td><input class="bx-auth-input form-control login-inp" readonly type="text" name="" maxlength="255" value="<?=$arResult["LAST_NAME"]?>" /></td>
            </tr>
            <tr>
                <td class="bx-auth-label">Имя</td>
                <td><input class="bx-auth-input form-control login-inp" readonly type="text" name="" maxlength="255" value="<?=$arResult["NAME"]?>" /></td>
            </tr>
            <tr>
                <td class="bx-auth-label">Отчество</td>
                <td><input class="bx-auth-input form-control login-inp" readonly type="text" name="" maxlength="255" value="<?=$arResult["SECOND_NAME"]?>" /></td>
            </tr>
            <tr>
                <td class="bx-auth-label">Компания</td>
                <td><input class="bx-auth-input form-control login-inp" readonly type="text" name="" maxlength="255" value="<?=$arResult['COMPANY']?>" /></td>
            </tr>
            <tr>
                <td class="bx-auth-label">Департамент</td>
                <td><input class="bx-auth-input form-control login-inp" readonly type="text" name="" maxlength="255" value="<?=$arResult['UF_DEPARTMENT']?>" /></td>
            </tr>
        </table>

        <div class="log-popup-footer">
            <input type="submit" value="Подтвердить" class="login-btn" />
            
            <a class="login-link-forgot-pass" href="contact_to_admin.php">Обратиться к администратору</a>
        </div>
    </form>
</div>
<?endif?>

<?if ($arResult['repeat']===true || $arResult['step']===2):?>
<div class="bx-auth-2 ">

    <div class="bx-auth-title"><?=GetMessage("AUTH_PASSWORD_LABEL")?></div>

    <form name="form_auth" class="confirm-form"  target="_top" method="post" action="<?=POST_FORM_ACTION_URI?>">
        <input type="hidden" name="step" value="2" />
        <input type="hidden" name="USER_LOGIN" value="<?=$arResult['LOGIN']?>" />
        <input type="hidden" name="USER_ID" value="<?=$arResult['ID']?>" />
        <?if (strlen($arResult["BACKURL"]) > 0):?>
            <input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
        <?endif?>
        <?foreach ($arResult["POST"] as $key => $value):?>
            <input type="hidden" name="<?=$key?>" value="<?=$value?>" />
        <?endforeach?>

        <table class="bx-auth-table">
            <tr>
                <td class="bx-auth-label">Введите пароль*</td>
                <td><input class="bx-auth-input form-control password login-inp" type="password" id="password" name="password" maxlength="255" value="" /></td>
            </tr>
            <tr>
                <td class="bx-auth-label">Подтвердите пароль*</td>
                <td><input class="bx-auth-input form-control password-confirm login-inp" type="password" id="password-confirm" name="password-confirm" maxlength="255" value="" /></td>
            </tr>
        </table>
        <!--region mz-->
        <!--Показать пароль-->
        <div class="login-text login-item">
            <input type="checkbox" id="PASS_VIEW" name="PASS_VIEW" value="N" />
            <label class="login-item-checkbox-label" for="PASS_VIEW">Показать пароль</label>
        </div>
        <!--endregion-->
        <div class="log-popup-footer">
            <input type="submit" value="Сохранить" class="login-btn" />
        </div>
    </form>
</div>
<?endif?>

<script type="text/javascript">
    <?//if (strlen($arResult["LAST_LOGIN"])>0):?>
    //try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
    <?//else:?>
    //try{document.form_auth.USER_LOGIN.focus();}catch(e){}
    <?//endif?>

        // let firstBlock = document.querySelector('.bx-auth');
        // let secondBlock = document.querySelector('.bx-auth-2');
        // document.querySelector('.bx-auth .confirm-form').addEventListener('submit', function (e) {
        //     e.preventDefault();
        //     firstBlock.classList.add('hidden');
        //     secondBlock.classList.remove('hidden');
        //
        // });

        // $('.bx-auth-2 .confirm-form').submit(function (e) {
        //     e.preventDefault();
        //     let pass1 = document.querySelector('.bx-auth-2 .confirm-form .password').value;
        //     let pass2 = document.querySelector('.bx-auth-2 .confirm-form .password-confirm').value;
        //     if(pass1 === pass2){
        //         let str = $('.bx-auth-2 .confirm-form').serialize();
        //         console.log(window.location.href);
        //         $.ajax("/authorisation.php", {
        //             async: true,
        //             method: "POST",
        //             data: str,
        //             dataType: "json",
        //             complete: function (msg) {
        //                 console.log(msg.responseJSON);
        //
        //
        //             }
        //         });
        //     }
        //
        // });



</script>

<script type="text/javascript">
    //region mz Показать пароль
    BX.ready(function () {
        function show() {
            var p = document.getElementById('password');
            p.setAttribute('type', 'text');

            var s = document.getElementById('password-confirm');
            s.setAttribute('type', 'text');
        }

        function hide() {
            var p = document.getElementById('password');
            p.setAttribute('type', 'password');

            var s = document.getElementById('password-confirm');
            s.setAttribute('type', 'password');
        }

        var pwShown = 0;

        document.getElementById("PASS_VIEW").addEventListener("click", function () {
            if (pwShown == 0) {
                pwShown = 1;
                show();
            } else {
                pwShown = 0;
                hide();
            }
        }, false);
    });
    //endregion
</script>