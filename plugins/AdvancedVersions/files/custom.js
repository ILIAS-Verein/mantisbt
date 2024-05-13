const rpv_selector_label = 'label[for=product_version]';
const rpv_selector_update_version_input = '#version';
const rpv_selector_create_version_input = '#product_version';
const rpv_selector_update_form = '#update_bug_form';
const rpv_selector_notice = '#rpv_notice';
const rpv_html_notice = "<span id='rpv_notice' class='required'>&nbsp;Please select the affected product version or pick &quot;n.a.&quot; if not known.</span>";

$( document ).ready(function() {
    $(function () {
        if ($(rpv_selector_label).length > 0) {
            $(rpv_selector_label).before("<span class='required'>*</span>&nbsp;");
            $(rpv_selector_create_version_input).attr("required", '');
            $(rpv_selector_create_version_input).after(rpv_html_notice);
            $(rpv_selector_create_version_input + " option").filter(function() {
                return !this.value;
            }).text("(select)");
        }

        if ($(rpv_selector_update_form).length > 0
          && $(rpv_selector_update_version_input).length > 0
          && $(rpv_selector_update_version_input).val() !== "")
        {
            $(rpv_selector_update_version_input + " option").filter(function() {
                return !this.value;
            }).remove();
        }
        $(rpv_selector_create_version_input).change(function (){
            if($(rpv_selector_create_version_input).val() === "" && !$(rpv_selector_notice).length){
                $(rpv_selector_create_version_input).after(rpv_html_notice);

            }else if($(rpv_selector_notice).length > 0){
                $(rpv_selector_notice).remove();
            }
        });

        function handleDuplicates(data_group, target=null){
            if(target.val() == ""){
                return;
            }

            cf = 0;
            $("select[data_group='" + data_group + "']").each(function(){
                if($(this).val() == target.val()){
                    cf = cf + 1;
                }
            });

            if(cf > 1){
                target.css("border-color", "red");
                target.val(target.attr('data_check_val'));
                setTimeout(function(){target.css("border-color", '');}, 5000);
            }
        }

        if ($("label:contains('Also in Version')").length) {
            name_also_in_version = $("label:contains('Also in Version')").attr("for");
            selector_also_in_version = 'select[name="' + name_also_in_version + '[]"]';
            selector_product_version = '#' + $("label:contains('Product Version')").attr("for");


            customFieldProductVersionChange = function () {
                handleDuplicates('data_product_version', $(this));

                if ($(this).val() == "") {
                    if (!$(this).is(':last-child')) {
                        $(this).remove();
                    }
                } else {
                    last = $(selector_also_in_version + ":last");
                    // if there are no hidden create one
                    if (last.length && last.val() != "") {
                        copy = $(this).clone();
                        copy.val("")
                          .attr('data_check_val', "")
                          .css("border-color", '')
                          .change(customFieldProductVersionChange);
                        if (!copy.find('option[value=""]').length) {
                            copy.prepend('<option value=""></option>');
                        }
                        $(this).parent().append(" ").append(copy);
                    }
                }
                $(this).attr("data_check_val", $(this).val());
            };

            $(selector_product_version).attr('data_check_val', "").attr('data_group',
              'data_product_version');

            if ($("#" + name_also_in_version).val() != null) {
                $("#" + name_also_in_version).val().forEach(function (item) {
                    copy = $(selector_product_version).clone();
                    if (!copy.find('option[value=""]').length) {
                        copy.prepend('<option value=""></option>');
                    }
                    copy.val(item)
                      .attr('name', name_also_in_version + '[]')
                      .attr('required', null)
                      .attr('id', null)
                      .attr('data_check_val', "")
                      .change(customFieldProductVersionChange);
                    copy.find('option[value="n.a."]').remove();

                    $(selector_product_version).parent().append(copy);
                });
            }

            if ($(selector_also_in_version).length > 1 || ($(selector_product_version).val() != "" && $(
              selector_product_version).val() != "n.a.")) {
                copy = $(selector_product_version).clone();
                if (!copy.find('option[value=""]').length) {
                    copy.prepend('<option value=""></option>');
                }
                copy.val("")
                  .attr('name', name_also_in_version + '[]')
                  .attr('required', null)
                  .attr('id', null)
                  .attr('data_check_val', "")
                  .change(customFieldProductVersionChange);
                copy.find('option[value="n.a."]').remove();

                $(selector_product_version).after("</br><label>Also in Version:</label> ");
                $(selector_product_version).parent().append(" ").append(copy);
            }
            if ($("input[name='" + name_also_in_version + "_presence']").length) {
                $(selector_product_version)
                  .parent()
                  .prepend($("input[name='" + name_also_in_version + "_presence']"));
            }

            $("#" + name_also_in_version).parents('tr').first().remove();

            $(selector_product_version).change(function () {
                handleDuplicates('data_product_version', $(this));
                if ($(this).val() == "n.a." || $(this).val() == "") {
                    first = $(selector_also_in_version + ":first");
                    if (first.length) {
                        $(this).val(first.val());

                        if ($(selector_also_in_version).length > 1) {
                            first.remove();
                        } else {
                            if (first.val() == "") {
                                first.remove();
                                $(this).parent().find("label").remove();
                            } else {
                                first.val("");
                            }
                        }
                    }
                } else {
                    last = $(selector_also_in_version + ":last").first();
                    // if there are no hidden create one
                    if (!last.length || last.val() != "") {
                        copy = $(selector_product_version).clone();
                        if (!copy.find('option[value=""]').length) {
                            copy.prepend('<option value=""></option>');
                        }
                        copy.val("")
                          .attr('name', name_also_in_version + '[]')
                          .attr('required', null)
                          .attr('id', null)
                          .attr('data_check_val', "")
                          .css("border-color", '')
                          .change(customFieldProductVersionChange);
                        copy.find('option[value="n.a."]').remove();

                        $(this).parent().append("</br><label>Also in Version:</label> ").append(copy);
                    }
                }

                $(this).attr("data_check_val", $(this).val());

                if ($(rpv_selector_create_version_input).val() == "") {
                    $(rpv_selector_create_version_input).after(rpv_html_notice);
                } else if ($(rpv_selector_notice).length > 0) {
                    $(rpv_selector_notice).remove();
                }
            });

            $(selector_product_version).parents("form").first().submit(function () {
                $(selector_also_in_version).each(function () {
                    if ($(this).val() == "") {
                        $(this).remove();
                    }
                });
            });
        }


        if ($("label:contains('Also fixed in Version')").length || $(
          "th.category:contains('Also fixed in Version')").parent().find("select").length) {
            name_also_fixed_in_version = $("label:contains('Also fixed in Version')").attr("for");

            if (name_also_fixed_in_version == null) {
                name_also_fixed_in_version = $("th.category:contains('Also fixed in Version')")
                  .parent()
                  .find("select")
                  .attr("id");
            }

            selector_also_fixed_in_version = 'select[name="' + name_also_fixed_in_version + '[]"]';
            selector_fixed_in_version = "select[name='fixed_in_version']";

            customFieldFixedInVersionChange = function () {
                handleDuplicates('data_fixed_in_version', $(this));

                if ($(this).val() == "") {
                    if (!$(this).is(':last-child')) {
                        $(this).remove();
                    }
                } else {
                    last = $(selector_also_fixed_in_version + ":last");
                    // if there are no hidden create one
                    if (last.length && last.val() != "") {
                        copy = $(this).clone();
                        if (!copy.find('option[value=""]').length) {
                            copy.prepend('<option value=""></option>');
                        }
                        copy.val("")
                          .attr('data_check_val', "")
                          .css("border-color", '')
                          .change(customFieldFixedInVersionChange);

                        $(this).parent().append(" ").append(copy);
                    }
                }
                $(this).attr("data_check_val", $(this).val());
            };

            $(selector_fixed_in_version).attr('data_check_val', "").attr('data_group',
              'data_fixed_in_version');

            if ($("#" + name_also_fixed_in_version).val() != null) {
                $("#" + name_also_fixed_in_version).val().forEach(function (item) {
                    copy = $(selector_fixed_in_version).clone();
                    if (!copy.find('option[value=""]').length) {
                        copy.prepend('<option value=""></option>');
                    }
                    copy.val(item)
                      .attr('name', name_also_fixed_in_version + '[]')
                      .attr('required', null)
                      .attr('id', null)
                      .attr('data_check_val', "")
                      .change(customFieldFixedInVersionChange);
                    copy.find('option[value="n.a."]').remove();

                    $(selector_fixed_in_version).parent().append(copy);

                });
            }

            if ($(selector_also_fixed_in_version).length > 1 || ($(selector_fixed_in_version)
              .val() != "" && $(selector_fixed_in_version).val() != "n.a.")) {
                copy = $(selector_fixed_in_version).clone();
                if (!copy.find('option[value=""]').length) {
                    copy.prepend('<option value=""></option>');
                }
                copy.val("")
                  .attr('name', name_also_fixed_in_version + '[]')
                  .attr('required', null)
                  .attr('id', null)
                  .attr('data_check_val', "")
                  .change(customFieldFixedInVersionChange);
                copy.find('option[value="n.a."]').remove();

                $(selector_fixed_in_version).after("</br><label>Also fixed in Version:</label> ");
                $(selector_fixed_in_version).parent().append(" ").append(copy);
            }

            if ($("input[name='" + name_also_fixed_in_version + "_presence']").length) {
                $(selector_fixed_in_version)
                  .parent()
                  .prepend($("input[name='" + name_also_fixed_in_version + "_presence']"));
            }

            $("#" + name_also_fixed_in_version).parents('tr').first().remove();

            $(selector_fixed_in_version).change(function () {
                handleDuplicates('data_fixed_in_version', $(this));
                if ($(this).val() == "n.a." || $(this).val() == "") {
                    first = $(selector_also_fixed_in_version + ":first");
                    if (first.length) {
                        $(this).val(first.val());

                        if ($(selector_also_fixed_in_version).length > 1) {
                            first.remove();
                        } else {
                            if (first.val() == "") {
                                first.remove();
                                $(this).parent().find("label").remove();
                            } else {
                                first.val("");
                            }
                        }
                    }
                } else {
                    last = $(selector_also_fixed_in_version + ":last").first();
                    // if there are no hidden create one
                    if (!last.length || last.val() != "") {
                        copy = $(selector_fixed_in_version).clone();
                        if (!copy.find('option[value=""]').length) {
                            copy.prepend('<option value=""></option>');
                        }
                        copy.val("")
                          .attr('name', name_also_fixed_in_version + '[]')
                          .attr('required', null)
                          .attr('id', null)
                          .attr('data_check_val', "")
                          .css("border-color", '')
                          .change(customFieldFixedInVersionChange);
                        copy.find('option[value="n.a."]').remove();

                        $(this).parent().append("</br><label>Also fixed in Version:</label> ").append(
                          copy);
                    }
                }

                $(this).attr("data_check_val", $(this).val());
            });

            $(selector_fixed_in_version).parents("form").first().submit(function () {
                $(selector_also_fixed_in_version).each(function () {
                    if ($(this).val() == "") {
                        $(this).remove();
                    }
                });
            });
        }
    });
});