define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ask/notification/index',
                    add_url: 'ask/notification/add',
                    edit_url: 'ask/notification/edit',
                    del_url: 'ask/notification/del',
                    multi_url: 'ask/notification/multi',
                    table: 'ask_notification',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'from_user_id', title: __('From_user_id'), formatter: Table.api.formatter.search},
                        {field: 'fromuser.nickname', title: __('发件人'), operate: false},
                        {field: 'to_user_id', title: __('To_user_id'), formatter: Table.api.formatter.search},
                        {field: 'touser.nickname', title: __('收件人'), operate: false},
                        {field: 'action', title: __('Action'), formatter: Table.api.formatter.search},
                        {field: 'type', title: __('Type')},
                        {field: 'source_id', title: __('Source_id'), formatter: Table.api.formatter.search},
                        {field: 'title', title: __('Title')},
                        {field: 'isread', title: __('Isread'), formatter: Table.api.formatter.toggle, disable: true},
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
