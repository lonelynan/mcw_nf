  /**
   * 新增一个规格
   */
  function addSpec(obj)
  {   
    var num = $("#attrTable tr:last").find('input[name=logo_num]').val();
      num = Number(num) + 1;
      var src   = obj.parentNode.parentNode.parentNode;
      var idx   = rowindex(src);
      var tbl   = document.getElementById('attrTable');
      var row   = tbl.insertRow(idx - 1);
      var cell1 = row.insertCell(-1);
      var cell2 = row.insertCell(-1);
      var regx  = /<a([^>]+)<\/a>/i;
      cell1.className = 'label';
      var html = src.childNodes[0].innerHTML.replace(/(.*)(addSpec)(.*)(\[)(\+)/i, "$1removeSpec$3$4-");
      html = html.replace(/<input name=\"logo_num\" value=\"[0-9]*\" type=\"hidden\">/i, '<input name="logo_num" type="hidden" value="'+num+'">');
      html = html.replace(/<input name=\"logo_num\" type=\"hidden\" value=\"[0-9]*\">/i, '<input name="logo_num" type="hidden" value="'+num+'">');
      html = html.replace(/(attr_image_)[0-9]*/i, "attr_image_"+num+"");
      html = html.replace(/<div class=\"(LogoDetail_)[0-9]* img\">(.*)<\/div>/i, "<div class=\"LogoDetail_"+num+" img\"><\/div>");
      cell1.innerHTML = html;
      cell2.innerHTML = src.childNodes[1].innerHTML.replace(/readOnly([^\s|>]*)/i, '');

  }
  /**
   * 删除规格值
   */
  function removeSpec(obj)
  {
      var row = rowindex(obj.parentNode.parentNode.parentNode);
      var tbl = document.getElementById('attrTable');

      tbl.deleteRow(row);
  }

  /**
   * 处理规格
   */
  function handleSpec()
  {
      var elementCount = document.forms['theForm'].elements.length;
      for (var i = 0; i < elementCount; i++)
      {
          var element = document.forms['theForm'].elements[i];
          if (element.id.substr(0, 5) == 'spec_')
          {
              var optCount = element.options.length;
              var value = new Array(optCount);
              for (var j = 0; j < optCount; j++)
              {
                  value[j] = element.options[j].value;
              }

              var hiddenSpec = document.getElementById('hidden_' + element.id);
              hiddenSpec.value = value.join(String.fromCharCode(13)); // 用回车键隔开每个规格
          }
      }
      return true;
  }
  
