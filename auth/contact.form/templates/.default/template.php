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
<?if (!$arResult['send']===true):?>
<div class="bx-auth">

    <div class="bx-auth-title">Отправить обращение администратору:</div>



    <form name="form_auth" method="post" target="_top" class="confirm-form" action="<?=POST_FORM_ACTION_URI?>">
        <table class="bx-auth-table">
            <tr>
                <td class="bx-auth-label">Выбор проблемы</td>
                <td>
                    <select name="problem" >
                        <option value="1">Неверные личные данные</option>
                        <option value="2">Восстановление пароля</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bx-auth-label">Текст обращения</td>
                <td>
                    <textarea class="bx-auth-textarea form-control" name="text" id="" cols="60" rows="3" placeholder="Пожалуйста, опишите подробно Вашу проблему"></textarea>
                </td>
            </tr>
            <tr>
                <td class="bx-auth-label">Контактные данные</td>
                <td>
                    <textarea class="bx-auth-textarea form-control" name="contact-info" id="" cols="60"  rows="3" placeholder="Пожалуйста, укажите константы для связи(email, имя, телефон и т.д)"></textarea>
                </td>
            </tr>
        </table>

        <div class="log-popup-footer">
            <input type="submit" value="Отправить" class="login-btn" />

            <a class="login-link-forgot-pass" href="<?=$arResult['back_url']?>">Отмена</a>
        </div>
    </form>
</div>
<?endif?>

<?if ($arResult['send']===true):?>
<div class="bx-auth">
    <div class="">Ваше обращение отправлено. Ожидайте, пока администратор свяжется с Вами!</div>
</div>
<?endif?>






<script type="text/javascript">

</script>