<?php
echo empty($options['noContainer']) ? '<div class="form-group">' : '';
echo $label !== false ? "<label>{$label}</label>" : '';
$uid = Tools::randomString();
?>
<div id='map<?= $uid; ?>' class="formMap"  style="width: 100%; height: 400px"></div>
<script>
    var myMap<?= $uid; ?>;
    inji.onLoad(function () {
        ymaps.ready(init<?= $uid; ?>);

        function init<?= $uid; ?>() {

            var myPlacemark;
            myMap<?= $uid; ?> = new ymaps.Map("map<?= $uid; ?>", {
                center: ["<?= !empty($options['value']['lat']) ? $options['value']['lat'] : '55.76'; ?>", "<?= !empty($options['value']['lng']) ? $options['value']['lng'] : '37.64'; ?>"],
                zoom: 13
            });

<?php
if (!empty($options['value']['lat']) && !empty($options['value']['lng'])) {
    /*
      ?>
      myPlacemark = new ymaps.Placemark([<?= $options['value']['lat']; ?>,<?= $options['value']['lng']; ?>], {
      // Чтобы балун и хинт открывались на метке, необходимо задать ей определенные свойства.
      //balloonContentHeader: "",
      //balloonContentBody: "",
      //balloonContentFooter: "Подвал",
      });
      myMap.geoObjects.add(myPlacemark);
      <?php */
}
?>
            myMap<?= $uid; ?>.events.add('click', function (e) {
                console.log(e);
                console.log(e.get('coordPosition'));
                var coords = e.get('coordPosition');
                myMap<?= $uid; ?>.balloon.open(coords, {
                    contentHeader: 'Событие!',
                    contentBody: '<p>Кто-то щелкнул по карте.</p>' +
                            '<p>Координаты щелчка: ' + [
                                coords[0].toPrecision(6),
                                coords[1].toPrecision(6)
                            ].join(', ') + '</p>',
                    contentFooter: '<sup>Щелкните еще раз</sup>'
                });
            });
        }
    });
</script>
<input type ="hidden" name = '<?= $name; ?>[lat]' value = '<?= !empty($options['value']['lat']) ? addcslashes($options['value']['lat'], "'") : ''; ?>' />
<input type ="hidden" name = '<?= $name; ?>[lng]' value = '<?= !empty($options['value']['lng']) ? addcslashes($options['value']['lng'], "'") : ''; ?>' />
<?php
echo!empty($options['helpText']) ? "<div class='help-block'>{$options['helpText']}</div>" : '';
echo empty($options['noContainer']) ? '</div>' : '';
?>