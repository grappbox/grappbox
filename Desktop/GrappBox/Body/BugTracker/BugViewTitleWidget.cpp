#include "BugViewTitleWidget.h"

BugViewTitleWidget::BugViewTitleWidget(int bugId, QString title, QWidget *parent) : QWidget(parent)
{
    QLabel *lblTitle = new QLabel(tr("View Issue : ") + title);
    _bugID = bugId;
    _mainLayout = new QHBoxLayout();
    _btnEdit = new QPushButton(tr("Edit"));
    _btnClose = new QPushButton(tr("Close"));

    QObject::connect(_btnClose, SIGNAL(released()), this, SLOT(TriggerCloseIssue()));
    QObject::connect(_btnEdit, QPushButton::released, [=] { emit OnIssueEdit(_bugID); });

    _mainLayout->addWidget(lblTitle);
    _mainLayout->addWidget(_btnEdit);
    _mainLayout->addWidget(_btnClose);
    this->setLayout(_mainLayout);
}

void BugViewTitleWidget::TriggerCloseIssue()
{
    //TODO : Link API
    emit OnIssueClosed(_bugID);
}
