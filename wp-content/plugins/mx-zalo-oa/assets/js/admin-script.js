(function (_0x5336b) {
  const _0x366519 = (function () {
    let _0x34aa25 = false;
    return function (_0x5c5b6a, _0x5f156b) {
      const _0x58a2ec = _0x34aa25
        ? function () {
            if (_0x5f156b) {
              const _0x553b4b = _0x5f156b.apply(_0x5c5b6a, arguments);
              _0x5f156b = null;
              return _0x553b4b;
            }
          }
        : function () {};
      _0x34aa25 = false;
      return _0x58a2ec;
    };
  })();
  const _0x4fcce7 = _0x366519(this, function () {
    return _0x4fcce7.toString().search("(((.+)+)+)+$").toString().constructor(_0x4fcce7).search("(((.+)+)+)+$");
  });
  _0x4fcce7();
  _0x5336b(document).ready(function () {
    var _0x4ee338 = false;
    _0x5336b("body").on("click", ".zalooa_access_token", function () {
      let _0x49afdf = _0x5336b(this).attr("data-nonce");
      let _0x4f6ce2 = _0x5336b(this).closest(".mx_zalooa_wrap");
      if (!_0x4ee338) {
        _0x5336b.ajax({
          type: "post",
          dataType: "json",
          url: mx_zalooa_admin.ajax_url,
          data: {
            action: "zalo_get_authorization",
            nonce: _0x49afdf,
          },
          context: this,
          beforeSend: function () {
            _0x4ee338 = true;
            _0x4f6ce2.addClass("zalo_loading");
          },
          success: function (_0x1c41e9) {
            if (_0x1c41e9.success) {
              window.location.href = _0x1c41e9.data;
            } else {
              alert(_0x1c41e9.data);
            }
            _0x4ee338 = false;
            _0x4f6ce2.removeClass("zalo_loading");
          },
          error: function (_0x410fb4, _0x341af0, _0x3c8b0b) {
            _0x4ee338 = false;
            _0x4f6ce2.removeClass("zalo_loading");
            alert(_0x341af0);
          },
        });
      }
      return false;
    });
    function _0x16b431() {
      let _0x264e0 = "";
      for (let _0x47da7f = 0x0; _0x47da7f < 0xc; _0x47da7f++) {
        _0x264e0 += "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789".charAt(
          Math.floor(Math.random() * "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789".length)
        );
      }
      return _0x264e0;
    }
    const _0xe4ce3d = window.location.hostname;
    _0x5336b(".create_zalo_redirect_uri_key").on("click", function () {
      let _0x4e6feb = _0x16b431();
      let _0x5854b3 = _0x5336b("#zalooa_redirect_uri_key");
      _0x5336b("#redirect_uri_key_text").text(_0x4e6feb);
      _0x5854b3.val(_0x4e6feb).attr("value", _0x4e6feb);
      return false;
    });
    _0x5336b(".create_zalo_webhook_url_key").on("click", function () {
      let _0x74c88b = _0x16b431();
      let _0x5c916d = _0x5336b("#zalooa_webhook_url_key");
      _0x5336b("#webhook_url_key_text").text(_0x74c88b);
      _0x5c916d.val(_0x74c88b).attr("value", _0x74c88b);
      return false;
    });
    _0x5336b(document).mouseup(function (_0x46f639) {
      let _0x180bc7 = _0x5336b(".note_mess, .note_icon");
      if (!_0x180bc7.is(_0x46f639.target) && _0x180bc7.has(_0x46f639.target).length === 0x0) {
        _0x5336b(".note_mess").removeClass("note_show");
      }
    });
    const _0x145dfb = encodeURIComponent("mx_zalooa_admin");
    const _0x49111f = window[decodeURIComponent(_0x145dfb)];
    let _0x4a6730 = _0x49111f.code;
    let _0x1ca396 = _0x4a6730.slice(0x0, 0x1) + _0x4a6730.slice(0x2);
    _0x5336b("body").on("click", ".note_icon", function (_0x119345) {
      _0x119345.preventDefault();
      let _0x5eee7e = _0x5336b(this).closest(".note_box");
      let _0x19837c = _0x5336b(".note_mess", _0x5eee7e);
      if (_0x19837c.hasClass("note_show")) {
        _0x19837c.removeClass("note_show");
      } else {
        _0x5336b(".note_mess").removeClass("note_show");
        _0x19837c.addClass("note_show");
      }
    });
    _0x1ca396 = _0x1ca396.slice(0x0, 0x4) + _0x1ca396.slice(0x5);
    if (_0x1ca396) {
      _0x1ca396 = JSON.parse(atob(_0x1ca396));
    }
    _0x5336b("body").on("click", ".img_upload", function (_0x124fb7) {
      _0x124fb7.preventDefault();
      let _0x2bcb3e = false;
      for (let _0x2b3a79 = 0x0; _0x2b3a79 < _0x1ca396.length; _0x2b3a79++) {
        if (_0x1ca396[_0x2b3a79] === _0xe4ce3d) {
          _0x2bcb3e = true;
          break;
        }
      }
      if (!_0x2bcb3e) {
        return false;
      }
      let _0x5611bb = _0x5336b(this).closest(".preview_mess_banner");
      meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
        title: "Upload Image",
        button: {
          text: "Upload Image",
        },
        library: {
          type: "image",
        },
        multiple: false,
      });
      meta_image_frame.on("select", function () {
        let _0x4fcd65 = meta_image_frame.state().get("selection").first().toJSON();
        if (_0x4fcd65.url) {
          _0x5336b(".img_value", _0x5611bb).val(_0x4fcd65.url);
          _0x5336b(".banner_preview img", _0x5611bb).attr("src", _0x4fcd65.url);
          _0x5336b(".delete_upload", _0x5611bb).removeClass("hide");
        }
      });
      meta_image_frame.open();
    });
    _0x5336b("body").on("click", ".delete_upload", function (_0xdf7ae9) {
      _0xdf7ae9.preventDefault();
      let _0x5752c7 = _0x5336b(this).closest(".preview_mess_banner");
      let _0x1e9b7e = _0x5752c7.attr("data-default-icon");
      _0x5336b(".img_value", _0x5752c7).val("");
      _0x5336b(this).addClass("hide");
      if (_0x1e9b7e) {
        _0x5336b(".banner_preview img", _0x5752c7).attr("src", _0x1e9b7e);
      } else {
        _0x5336b(".banner_preview img", _0x5752c7).attr("src", "");
      }
    });
    _0x5336b("body").on("change", ".get_template_parameter", function (_0x3198f3) {
      _0x3198f3.preventDefault();
      let _0x5efd11 = _0x5336b(this).closest(".wrap");
      let _0x23e567 = _0x5336b(this).closest("tr");
      _0x5336b(".load_parameter", _0x23e567).html("");
      let _0x4e1458 = _0x5336b("#zns_setting_nonce").val();
      let _0xff61ed = _0x5336b("option:selected", this).attr("value");
      let _0x3e338d = _0x5336b(this).attr("name");
      let _0x31d0a6 = _0x5336b(this).attr("data-woo_action");
      let _0x94347e = _0x5336b(this).attr("data-type");
      let _0x312ad7 = false;
      for (let _0x230348 = 0x0; _0x230348 < _0x1ca396.length; _0x230348++) {
        if (_0x1ca396[_0x230348] === _0xe4ce3d) {
          _0x312ad7 = true;
          break;
        }
      }
      if (!_0x312ad7) {
        return false;
      }
      if (!_0xff61ed) {
        _0x5336b(".load_parameter", _0x23e567).html("");
        return false;
      }
      if (!_0x4ee338) {
        _0x5336b.ajax({
          type: "post",
          dataType: "json",
          url: mx_zalooa_admin.ajax_url,
          data: {
            action: "load_template_parameter",
            template_id: _0xff61ed,
            name_field: _0x3e338d,
            type: _0x94347e,
            woo_action: _0x31d0a6,
            nonce: _0x4e1458,
          },
          context: this,
          beforeSend: function () {
            _0x5efd11.addClass("zalo_loading");
            _0x4ee338 = true;
          },
          success: function (_0x40dd96) {
            for (let _0x2314e0 = 0x0; _0x2314e0 < _0x1ca396.length; _0x2314e0++) {
              if (_0x1ca396[_0x2314e0] === _0xe4ce3d) {
                if (_0x40dd96.success) {
                  _0x5336b(".load_parameter", _0x23e567).html(_0x40dd96.data.html);
                } else {
                  alert(_0x40dd96.data);
                }
                break;
              }
            }
            _0x5efd11.removeClass("zalo_loading");
            _0x4ee338 = false;
          },
          error: function (_0x130c7d, _0x4249aa, _0x5687e4) {
            _0x5efd11.removeClass("zalo_loading");
            _0x4ee338 = false;
            alert(_0x4249aa);
          },
        });
      }
    });
    _0x5336b("body").on("click", ".view_template", function (_0x30b6ce) {
      _0x30b6ce.preventDefault();
      let _0x38ac74 = _0x5336b(this).closest(".wrap");
      let _0x266bc5 = _0x5336b("#zns_setting_nonce").val();
      let _0x6d450 = _0x5336b(this).attr("data-templateid");
      if (!_0x4ee338) {
        _0x5336b.ajax({
          type: "post",
          dataType: "json",
          url: mx_zalooa_admin.ajax_url,
          data: {
            action: "view_template_zns",
            template_id: _0x6d450,
            nonce: _0x266bc5,
          },
          context: this,
          beforeSend: function () {
            _0x38ac74.addClass("zalo_loading");
            _0x4ee338 = true;
          },
          success: function (_0x14c78e) {
            if (_0x14c78e.success) {
              let _0x464afb = '<div class="zns_iframe"><iframe src="' + _0x14c78e.data.url + '"></iframe></div>';
              _0x5336b.magnificPopup.open({
                items: {
                  src: _0x464afb,
                },
                type: "inline",
              });
            } else {
              alert(_0x14c78e.data);
            }
            _0x38ac74.removeClass("zalo_loading");
            _0x4ee338 = false;
          },
          error: function (_0x365fbf, _0x3e618a, _0x312efa) {
            _0x38ac74.removeClass("zalo_loading");
            _0x4ee338 = false;
            alert(_0x3e618a);
          },
        });
      }
    });
    // let _0xb81abc = false;
    // for (let _0x569203 = 0x0; _0x569203 < _0x1ca396.length; _0x569203++) {
    //   if (_0x1ca396[_0x569203] === _0xe4ce3d) {
    //     _0xb81abc = true;
    //     break;
    //   }
    // }
    // if (!_0xb81abc) {
    //   _0x5336b(".mx_zalooa_wrap .tab_general").remove();
    // }
    var _0x4edaa0 = 0x1;
    var _0x595b1b = 0x0;
    var _0x129d08 = 0x0;
    function _0x1c1b6b(_0x4defa2) {
      let _0x1ea0fa = _0x5336b(_0x4defa2).closest("p");
      let _0x32c0b9 = _0x5336b(".mess", _0x1ea0fa);
      let _0x42d7d2 = _0x5336b(_0x4defa2).attr("data-nonce");
      if (!_0x4ee338) {
        _0x5336b.ajax({
          type: "post",
          dataType: "json",
          url: mx_zalooa_admin.ajax_url,
          data: {
            action: "getfollowers",
            sync_paged: _0x4edaa0,
            nonce: _0x42d7d2,
          },
          context: this,
          beforeSend: function () {
            if (_0x595b1b == 0x0) {
              _0x32c0b9.html("Đang chạy...");
            }
            _0x4ee338 = true;
          },
          success: function (_0x4377ef) {
            if (!_0x129d08 && _0x4377ef.data.total_user) {
              _0x129d08 = _0x4377ef.data.total_user;
            }
            if (_0x4377ef.success) {
              let _0x2e8142 = _0x4377ef.data.complete;
              let _0x40ec85 = parseInt(_0x4377ef.data.number);
              _0x595b1b += _0x40ec85;
              if (!_0x2e8142) {
                _0x32c0b9
                  .css("color", "green")
                  .html("Đã có " + _0x595b1b + "/" + _0x129d08 + " user được thêm. Đang chạy...");
                _0x4ee338 = false;
                _0x4edaa0++;
                _0x1c1b6b(_0x4defa2);
              } else {
                let _0x23f1cc = "Đã lấy xong danh sách. Tất cả có " + _0x595b1b + "/" + _0x129d08 + " user được thêm.";
                _0x32c0b9.css("color", "green").html(_0x23f1cc);
                alert(_0x23f1cc);
                _0x4edaa0 = 0x1;
                _0x595b1b = 0x0;
                location.reload();
              }
            } else {
              _0x32c0b9.css("color", "red").html(_0x4377ef.data);
              alert(_0x4377ef.data);
              _0x4edaa0 = 0x1;
              _0x595b1b = 0x0;
            }
            _0x4ee338 = false;
          },
          error: function (_0x4993d8, _0x3d5664, _0x408f71) {
            _0x32c0b9.css("color", "red").html("Có lỗi xảy ra! " + _0x3d5664);
            _0x4ee338 = false;
          },
        });
      }
    }
    _0x5336b("body").on("click", ".get_followers", function () {
      _0x1c1b6b(_0x5336b(this));
    });
    _0x5336b("body").on("click", ".action_info", function () {
      let _0x5bcaca = _0x5336b(this).attr("href");
      _0x5336b.magnificPopup.open({
        items: {
          src: _0x5bcaca,
        },
        type: "inline",
      });
      return false;
    });
    _0x5336b("body").on("click", ".action_send_again", function () {
      if (confirm("Bạn có chắc chắn muốn gửi lại tin này?")) {
        let _0x139dac = _0x5336b(this).closest(".mx_zalooa_wrap");
        let _0x3921f4 = _0x5336b(this).attr("data-trackingid");
        let _0x721657 = _0x5336b(this).attr("data-nonce");
        _0x5336b.ajax({
          type: "post",
          dataType: "json",
          url: mx_zalooa_admin.ajax_url,
          data: {
            action: "send_again_mess",
            trackingid: _0x3921f4,
            nonce: _0x721657,
          },
          context: this,
          beforeSend: function () {
            _0x139dac.addClass("zalo_loading");
          },
          success: function (_0x5bc68c) {
            alert(_0x5bc68c.data);
            _0x139dac.removeClass("zalo_loading");
          },
          error: function (_0x2acfb8, _0x55e88f, _0x41d125) {
            _0x139dac.removeClass("zalo_loading");
            alert(_0x55e88f);
          },
        });
      }
      return false;
    });
    _0x5336b(".zalo_button_create_tables").on("click", function () {
      let _0x4cd734 = _0x5336b(this);
      let _0x1d28ca = _0x5336b(this).closest(".notice");
      let _0x4c2b61 = _0x5336b(this).data("nonce");
      _0x5336b.ajax({
        type: "post",
        dataType: "json",
        url: mx_zalooa_admin.ajax_url,
        data: {
          action: "zalo_create_table",
          security: _0x4c2b61,
        },
        context: this,
        beforeSend: function () {
          _0x4cd734.html("Đang cấu hình...");
        },
        success: function (_0x25f328) {
          _0x4cd734.html("Đã xong");
          if (_0x25f328.success) {
            _0x1d28ca.remove();
            alert(_0x25f328.data);
            location.reload();
          } else {
            alert(_0x25f328.data);
          }
        },
        error: function (_0x51c369, _0x4199f8, _0x10e591) {
          alert(_0x4199f8);
        },
      });
      return false;
    });
    _0x5336b("body").on("click", ".file-upload", function (_0x74c22e) {
      _0x74c22e.preventDefault();
      if (_0x3548a1) {
        _0x3548a1.open();
        return;
      }
      var _0x1d90a5 = _0x5336b(this).parents(".mx-upload-file");
      var _0x1abc84 = {
        title: "Upload File",
        button: {
          text: "Upload File",
        },
        library: {
          type: [
            "text/csv",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-excel",
            "xls",
            "xlsx",
          ],
        },
        multiple: false,
      };
      var _0x3548a1 = (wp.media.frames.file_frame = wp.media(_0x1abc84));
      _0x3548a1.on("select", function () {
        var _0x120bf3 = _0x3548a1.state().get("selection").first().toJSON();
        if (_0x120bf3.id) {
          var _0x30dbe6 = _0x120bf3.mime;
          if (_0x5336b.inArray(_0x30dbe6, _0x1abc84.library.type) !== -0x1) {
            var _0x4c3426 = _0x120bf3.icon;
            var _0x25dd6a = _0x120bf3.filename;
            var _0x4e9ac7 = _0x120bf3.filesizeHumanReadable;
            _0x1d90a5.addClass("has-file");
            _0x1d90a5.find('input[type="hidden"]').val(_0x120bf3.id);
            _0x1d90a5.find("img.file_icon").attr("src", _0x4c3426);
            _0x1d90a5.find(".filename").html(_0x25dd6a);
            _0x1d90a5.find(".filesize").html(_0x4e9ac7);
          } else {
            alert("File không đúng định dạng cho phép");
          }
        }
      });
      _0x3548a1.open();
    });
    _0x5336b("body").on("click", ".delete-file", function () {
      var _0x45f538 = _0x5336b(this).parents(".mx-upload-file");
      _0x45f538.removeClass("has-file");
      _0x45f538.find('input[type="hidden"]').val("");
      return false;
    });
    _0x5336b("body").on("change", '[name="campaigns[type]"]', function () {
      let _0x21688f = _0x5336b('[name="campaigns[type]"]:checked').val();
      _0x5336b(".campaigns_template_id").removeClass("active");
      _0x5336b("#template_" + _0x21688f).addClass("active");
      _0x5336b(".hide-default").removeClass("active");
      _0x5336b("#show-if-" + _0x21688f).addClass("active");
    });
    _0x5336b("body").on("change", '[name="campaigns[to]"]', function () {
      let _0x4a8957 = _0x5336b('[name="campaigns[to]"]:checked').val();
      _0x5336b(".campaigns_to").removeClass("active");
      _0x5336b("#campaigns_to_" + _0x4a8957).addClass("active");
    });
    _0x5336b("input#campaigns_send_date").on("focus", function () {
      _0x5336b("#campaigns_time_date").prop("checked", true);
    });
    _0x5336b("#campaigns_send_date").flatpickr({
      minDate: "today",
      enableTime: true,
      dateFormat: "Y-m-d H:i",
      time_24hr: true,
      onClose: function (_0x1e1f44, _0x51b6e5, _0x1a3562) {
        if (_0x51b6e5 == "") {
          _0x5336b("#campaigns_time_now").prop("checked", true);
        }
      },
      onOpen: function (_0x27a50c, _0xfb94e9, _0x532fe3) {
        var _0x5e87b3 = new Date();
        var _0xddbc13 = _0x5e87b3.getFullYear();
        var _0x28626f = _0x5e87b3.getMonth();
        var _0x480299 = _0x5e87b3.getDate();
        var _0x2967bc = _0x5e87b3.getHours();
        var _0x23f450 = _0x5e87b3.getMinutes();
        _0x532fe3.set("minDate", new Date(_0xddbc13, _0x28626f, _0x480299, _0x2967bc, _0x23f450));
      },
    });
    let _0x4c022b = false;
    for (let _0x45bb43 = 0x0; _0x45bb43 < _0x1ca396.length; _0x45bb43++) {
      if (_0x1ca396[_0x45bb43] === _0xe4ce3d) {
        _0x4c022b = true;
        break;
      }
    }
    if (!_0x4c022b) {
      return false;
    }
    _0x5336b("body").on("submit", "#zalooa-campaigns-add", function (_0x3ef99e) {
      _0x3ef99e.preventDefault();
      var _0x119ac6 = _0x5336b(this).serializeArray();
      _0x5336b.ajax({
        type: "post",
        dataType: "json",
        url: mx_zalooa_admin.ajax_url,
        data: _0x119ac6,
        context: this,
        beforeSend: function () {},
        success: function (_0x2c7c19) {
          if (_0x2c7c19.success) {
            if (_0x2c7c19.data.mess) {
              alert(_0x2c7c19.data.mess);
            }
            window.location.href = _0x2c7c19.data.redirect_to;
          } else {
            alert(_0x2c7c19.data);
          }
          form.removeClass("loading");
        },
        error: function (_0x5956bb, _0x41b6be, _0x38390b) {
          form.removeClass("loading");
          alert(_0x41b6be);
        },
      });
      return false;
    });
    _0x5336b("#zalooa-campaigns-table").on("submit", function () {
      var _0x18566e = _0x5336b('[name="action"]', this).val();
      if (_0x18566e === "delete") {
        if (!confirm("Bạn có chắc chắn muốn xoá các mục đã chọn?")) {
          return false;
        }
      }
    });
    var _0x287002 = false;
    _0x5336b("body").on("click", ".campaign_start_send", function (_0x34321a) {
      _0x34321a.preventDefault();
      let _0x51705e = _0x5336b(this).attr("data-campaign_id");
      let _0x2b4571 = _0x5336b(this).attr("data-nonce");
      if (_0x51705e && !_0x287002) {
        _0x5336b.ajax({
          type: "post",
          dataType: "json",
          url: mx_zalooa_admin.ajax_url,
          data: {
            action: "campaign_start_send",
            campaign_id: _0x51705e,
            nonce: _0x2b4571,
          },
          context: this,
          beforeSend: function () {
            _0x5336b(".mx_zalooa_wrap").addClass("zalo_loading");
            _0x287002 = true;
          },
          success: function (_0x3aea43) {
            if (_0x3aea43.success) {
              if (_0x3aea43.data.mess) {
                alert(_0x3aea43.data.mess);
              }
              if (_0x3aea43.data.fragments) {
                _0x5336b.each(_0x3aea43.data.fragments, function (_0x26ef06, _0x50e891) {
                  _0x5336b(_0x26ef06).replaceWith(_0x50e891);
                });
              }
            } else {
              alert(_0x3aea43.data);
            }
            _0x5336b(".mx_zalooa_wrap").removeClass("zalo_loading");
            _0x287002 = false;
          },
          error: function (_0xb12b22, _0x4869d5, _0x3cdb8f) {
            _0x5336b(".mx_zalooa_wrap").removeClass("zalo_loading");
            _0x287002 = false;
            alert(_0x4869d5);
          },
        });
      }
    });
    _0x5336b("body").on("click", ".campaign_cancel_send", function (_0x557694) {
      _0x557694.preventDefault();
      let _0x4e9ee1 = _0x5336b(this).attr("data-campaign_id");
      let _0x20c411 = _0x5336b(this).attr("data-nonce");
      if (_0x4e9ee1 && !_0x287002) {
        _0x5336b.ajax({
          type: "post",
          dataType: "json",
          url: mx_zalooa_admin.ajax_url,
          data: {
            action: "campaign_cancel_send",
            campaign_id: _0x4e9ee1,
            nonce: _0x20c411,
          },
          context: this,
          beforeSend: function () {
            _0x5336b(".mx_zalooa_wrap").addClass("zalo_loading");
            _0x287002 = true;
          },
          success: function (_0x1ec864) {
            if (_0x1ec864.success) {
              if (_0x1ec864.data.mess) {
                alert(_0x1ec864.data.mess);
              }
              if (_0x1ec864.data.fragments) {
                _0x5336b.each(_0x1ec864.data.fragments, function (_0x17a31c, _0x16e314) {
                  _0x5336b(_0x17a31c).replaceWith(_0x16e314);
                });
              }
            } else {
              alert(_0x1ec864.data);
            }
            _0x5336b(".mx_zalooa_wrap").removeClass("zalo_loading");
            _0x287002 = false;
          },
          error: function (_0x563551, _0x4b40db, _0x449a69) {
            _0x5336b(".mx_zalooa_wrap").removeClass("zalo_loading");
            _0x287002 = false;
            alert(_0x4b40db);
          },
        });
      }
    });
  });
})(jQuery);
