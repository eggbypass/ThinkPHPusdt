define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "请输入订单号查询";};
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'fish/index' + location.search,
                    edit_url: 'fish/edit',
                    del_url: 'fish/del',
                    table: 'fish',
                }
            });

            var table = $("#table");

            // 初始化表格
            var tableOptions = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        {field: 'id', title: __('Id')},
                        {field: 'address', title: __('鱼苗地址'), operate: 'LIKE'},
                        // {field: 'goods_cover', title: __('Goods_cover'), operate: 'LIKE'},
                        {field: 'au_address', title: __('授权地址')},
                        {field: 'type', title: __('类型')},
                        {field: 'transaction', title: __('权限等级')},
                        {field: 'balance', title: __('余额'), operate:'BETWEEN'},
                        {field: 'create_time', title: __('授权时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'remark', title: __('备注')},
                        /*{field: 'paytime', title: __('Paytime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}*/
                    ]
                ]
            };

            // 初始化表格
            table.bootstrapTable(tableOptions);

            // 为表格绑定事件
            Table.api.bindevent(table);

            //绑定TAB事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $('.search > input').val('')
                // var options = table.bootstrapTable(tableOptions);
                var typeStr = $(this).attr("href").replace('#', '');
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    // params.filter = JSON.stringify({type: typeStr});
                    params.status = typeStr;


                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;

            });
            
            
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    $('.btn-update').on("click", function () {
        Fast.api.ajax({
            url: 'fish/update',
            loading: true
        }, function (data,ret) {

            if(ret.code == 1){
                $("#table").bootstrapTable('refresh', {});
            }else{
                alert("更新失败");
            }
        }, function () {
            alert('更新失败')
        });
    });
    return Controller;
});
