define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ask/thanks/index',
                    add_url: 'ask/thanks/add',
                    edit_url: 'ask/thanks/edit',
                    del_url: 'ask/thanks/del',
                    multi_url: 'ask/thanks/multi',
                    table: 'ask_thanks',
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
                        {field: 'user_id', title: __('User_id'), formatter: Table.api.formatter.search},
                        {field: 'user.nickname', title: __('Nickname'), operate: false},
                        {field: 'type', title: __('Type'), searchList: {"question": __('Question'), "article": __('Article'), "answer": __('Answer')}, formatter: Table.api.formatter.normal},
                        {field: 'source_id', title: __('Source_id'), formatter: Table.api.formatter.search},
                        {field: 'orderid', title: __('Orderid')},
                        {field: 'money', title: __('Money'), operate: 'BETWEEN'},
                        {field: 'content', title: __('Content')},
                        {field: 'paytype', title: __('Paytype')},
                        {field: 'paytime', title: __('Paytime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {
                            field: 'view', title: __('View'), formatter: function (value, row, index) {
                                return '<a href="ask/' + row['type'] + '/edit/ids/' + row['source_id'] + '" class="btn btn-xs btn-info btn-dialog" title="查看">查看</a>'
                            }, operate: false
                        },
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"created": __('Created'), "paid": __('Paid')}, formatter: Table.api.formatter.status},
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