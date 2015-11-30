<?php

class UserFormsController extends Controller
{
    function getFormHtmlAction($userForm_id)
    {
        $userForm = \UserForms\Form::get((int) $userForm_id);
        if (!$userForm) {
            echo('form not found');
            return;
        }
        $form = new Ui\Form();
        $form->begin();
        if ($userForm->description) {
            echo "<p class = 'text-center'>{$userForm->description}</p>";
        }
        foreach ($userForm->inputs as $input) {
            $form->input($input->type, 'UserForms[' . (int) $userForm_id . '][input' . $input->id . ']', $input->label, ['required' => $input->required]);
        }
        ?>
        <button class = 'btn btn-success btn-block'>Отправить</button>
        </form>
        <?php
    }

}
?>
