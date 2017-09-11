<?php

$php = '';

$php .= file_get_contents('BFormElement.php');
$php .= file_get_contents('BFormer.php');
$php .= file_get_contents('BFormPage.php');
$php .= file_get_contents('BFormSection.php');
$php .= file_get_contents('BFormComponent.php');
$php .= file_get_contents('BFormComponentAddress.php');
$php .= file_get_contents('BFormComponentCreditCard.php');
$php .= file_get_contents('BFormComponentDate.php');
$php .= file_get_contents('BFormComponentDropDown.php');
$php .= file_get_contents('BFormComponentFile.php');
$php .= file_get_contents('BFormComponentHidden.php');
$php .= file_get_contents('BFormComponentHtml.php');
$php .= file_get_contents('BFormComponentLikert.php');
$php .= file_get_contents('BFormComponentMultipleChoice.php');
$php .= file_get_contents('BFormComponentName.php');
$php .= file_get_contents('BFormComponentSingleLineText.php');
$php .= file_get_contents('BFormComponentTextArea.php');

$php = str_ireplace('?>', '', $php);
$php = str_ireplace('<?php', '', $php);

echo '<?php'.$php.'?>';

?>