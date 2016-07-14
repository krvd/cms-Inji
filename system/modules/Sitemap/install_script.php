<?php

return function ($step = NULL, $params = []) {
    \App::$primary->config['moduleRouter']['sitemap.xml'] = 'Sitemap';
    \Config::save('app', App::$primary->config);
};
