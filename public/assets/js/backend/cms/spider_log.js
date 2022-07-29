define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/spider_log/index' + location.search,
                    add_url: 'cms/spider_log/add',
                    edit_url: 'cms/spider_log/edit',
                    del_url: 'cms/spider_log/del',
                    multi_url: 'cms/spider_log/multi',
                    import_url: 'cms/spider_log/import',
                    table: 'cms_spider_log',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'type', title: __('Type'), searchList: {"index": __('Index'), "archives": __('Archives'), "page": __('Page'), "special": __('Special'), "channel": __('Channel'), "diyform": __('Diyform'), "tag": __('Tag'), "user": __('User')}, formatter: Table.api.formatter.normal},
                        {field: 'aid', title: __('Aid'), sortable: true, formatter: Table.api.formatter.search},
                        {field: 'name', title: __('Name'), searchList: Config.nameList, formatter: Table.api.formatter.label},
                        {field: 'url', title: __('Url'), operate: 'LIKE', formatter: Table.api.formatter.url},
                        {field: 'nums', title: __('Nums'), sortable: true, formatter: Table.api.formatter.search, operate: 'BETWEEN'},
                        {field: 'lastdata', title: __('Lastdata'), operate: false, visible: false},
                        {field: 'firsttime', title: __('Firsttime'), sortable: true, operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'lasttime', title: __('Lasttime'), sortable: true, operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
    return Controller;
});
