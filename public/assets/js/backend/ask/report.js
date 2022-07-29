define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ask/report/index',
                    add_url: 'ask/report/add',
                    edit_url: 'ask/report/edit',
                    del_url: 'ask/report/del',
                    multi_url: 'ask/report/multi',
                    table: 'ask_report',
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
                        {field: 'user.nickname', title: __('nickname'), operate: false},
                        {field: 'type', title: __('Type'), searchList: {"question": __('Question'), "article": __('Article'), "answer": __('Answer'), "tag": __('Tag'), "comment": __('Comment')}, formatter: Table.api.formatter.normal},
                        {field: 'source_id', title: __('Source_id'), formatter: Table.api.formatter.search},
                        {field: 'reason', title: __('Reason'), formatter: Table.api.formatter.search},
                        {field: 'reason_text', title: __('Reason'), operate: false},
                        {field: 'content', title: __('Content')},
                        {field: 'ip', title: __('Ip')},
                        {field: 'useragent', title: __('Useragent'), visible: false},
                        {
                            field: 'view', title: __('View'), formatter: function (value, row, index) {
                                return '<a href="ask/' + row['type'] + '/edit/ids/' + row['source_id'] + '" class="btn btn-xs btn-info btn-dialog" title="查看">查看</a>'
                            }, operate: false
                        },
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal": __('Normal'), "hidden": __('Hidden')}, formatter: Table.api.formatter.status},
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
