<h2><?= $_GET['path']; ?></h2>
<div id="editor" style="height:500px"><?= htmlspecialchars($content); ?></div>
<button class="btn btn-primary" onclick="saveCode()">Сохранить</button>
<script>
    var editor;
    function saveCode() {
      if (editor) {
        inji.Server.request({
          data: editor.getValue(),
          method: 'POST'
        });
      }
    }
    inji.onLoad(function () {
      editor = ace.edit("editor");
    });
</script>