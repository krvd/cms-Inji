<?php

class TextBlocksController extends Controller
{
    function indexAction()
    {
        $this->view->setTitle('Текстовые блоки');
        $dataManager = new Ui\DataManager('TextBlocks\Block');
        $this->view->page(['data' => compact('dataManager')]);
    }

}
