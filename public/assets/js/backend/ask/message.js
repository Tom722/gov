define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ask/message/index',
                    add_url: 'ask/message/add',
                    edit_url: 'ask/message/edit',
                    del_url: 'ask/message/del',
                    multi_url: 'ask/message/multi',
                    table: 'ask_message',
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
                        {field: 'from_user_id', title: __('From_user_id'), formatter: Table.api.formatter.search},
                        {field: 'fromuser.nickname', title: __('发件人'), operate: false},
                        {field: 'to_user_id', title: __('To_user_id'), formatter: Table.api.formatter.search},
                        {field: 'touser.nickname', title: __('收件人'), operate: false},
                        {field: 'content', title: __('Content')},
                        {field: 'isread', title: __('Isread'), formatter: Table.api.formatter.toggle, disable: true},
                        {field: 'isfromdeleted', title: __('Isfromdeleted'), formatter: Table.api.formatter.toggle, disable: true},
                        {field: 'istodeleted', title: __('Istodeleted'), formatter: Table.api.formatter.toggle, disable: true},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
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