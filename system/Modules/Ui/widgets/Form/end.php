<div class="form-group">
    <button class ='btn btn-primary' 
            <?php
            foreach ($attributs as $attribute => $value) {
                echo " {$attribute} = '{$value}' ";
            }
            ?>
            ><?= $btnText; ?></button>
</div>
</form>