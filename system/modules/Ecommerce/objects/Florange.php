<?php

/**
 * Florange.ru catalog parser
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class FlorangeObject extends Object
{
    function processParseWeb($catalogUrl)
    {
        $option = Option::get('cio_code', 'gallery');
        if (!$option) {
            $option = new Option(['cio_code' => 'gallery', 'cio_type' => 'gallery', 'cio_name' => 'Фотографии']);
            $option->save();
        }
        $catalogUrl = base64_decode($catalogUrl);

        $catalog = Catalog::get(['catalog_imported', 'http://florange.ru' . $catalogUrl]);

        $this->simple_html_dom;
        $html = file_get_html('http://florange.ru' . $catalogUrl);

        if (!$catalog) {
            $catalog = new Catalog([
                'catalog_name' => $element = $html->find('.leftside .nav li a.current', 1)->innertext,
                'catalog_image' => $this->files->upload_from_url('http://florange.ru' . $html->find('.intro img', 0)->src),
                'catalog_parent' => 0,
                'catalog_prices' => 1,
                'catalog_imported' => 'http://florange.ru' . $catalogUrl
            ]);
            $catalog->save();
            $catalog->addRelation('options', $option->cio_id);
        }


        $i = 0;
        foreach ($html->find('ul li ul li ul li a') as $element) {
            $item = Item::get(['ci_imported', 'http://florange.ru' . $element->href]);
            if ($item)
                continue;

            $elem = file_get_html('http://florange.ru' . $element->href);

            $item = new Item();
            $item->ci_name = $element->innertext;
            $item->ci_catalog_id = $catalog->catalog_id;
            $item->ci_imported = 'http://florange.ru' . $element->href;
            $item->save();

            $prices = [];
            $i = 0;

            foreach ($elem->find('.description p') as $p) {
                if ($p->find('strong') && $p->find('strong', 0)->innertext != 'Выберите стиль:') {
                    //$item['head'][] = $p->find('strong', 0)->innertext;
                } elseif ($p->find('strong') && $p->find('strong', 0)->innertext == 'Выберите стиль:')
                    break;
                else
                    $prices[$i++]['ciprice_about'] = $p->innertext;
            }
            $i = 0;
            foreach ($elem->find('.description .tabcontent') as $t) {
                $prices[$i]['ciprice_sizes'] = '<table class="sizes">' . $t->find('.sizes', 0)->innertext . '</table>';
                $price = (is_object($t->find('.newprice', 0))) ? $t->find('.newprice', 0)->innertext : ((is_object($t->find('.newprice1', 0))) ? $t->find('.newprice1', 0)->innertext : '');
                $prices[$i++]['ciprice_price'] = substr($price, 0, strpos($price, '<'));
            }
            $i = 0;
            foreach ($elem->find('ul.tabs li') as $l) {
                $prices[$i++]['ciprice_name'] = $l->find('img', 0)->title;
            }

            $gallery = [];

            foreach ($elem->find('#slide img') as $img) {
                $gallery[] = $this->files->upload_from_url('http://florange.ru' . $img->src);
            }
            $galleryParam = new ItemParam([
                'cip_value' => implode(',', $gallery),
                'cip_ci_id' => $item->ci_id,
                'cip_cio_id' => $option->cio_id
            ]);
            $galleryParam->save();
            foreach ($prices as $price) {
                $price['ciprice_ci_id'] = $item->ci_id;
                $price = new ItemPrice($price);
                $price->save();
            }
        }
        return 'success';
    }

    function getCatalogs($parent = '')
    {
        $this->simple_html_dom;
        $catalogs = [];
        if ($parent === '') {
            $html = file_get_html('http://florange.ru/production/style/pushup/');

            foreach ($html->find('.leftside .nav>li>a') as $element) {
                if (strpos($element->href, '/style/'))
                    continue;
                $catalogs[] = ['name' => $element->innertext, 'href' => 'http://florange.ru' . $element->href];
            }
        }
        else {
            $html = file_get_html('http://florange.ru/production/style/pushup/');
            $element = $html->find('.leftside .nav>li>a', $parent);
            $html = file_get_html('http://florange.ru' . $element->href);
            $cat = $html->find('.leftside .nav li', $parent);
            foreach ($cat->find('li a') as $element) {
                $catalogs[base64_encode($element->href)] = ['name' => $element->innertext, 'href' => 'http://florange.ru' . $element->href];
            }
        }
        return $catalogs;
    }

}
