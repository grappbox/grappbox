#include "BugViewTitleWidget.h"

BugViewTitleWidget::BugViewTitleWidget(QString title, bool creation, QWidget *parent) : QWidget(parent)
{
    QString style;
    _title = new QLineEdit(tr("View Issue : ") + title);
    _bugID = -1;
    _mainLayout = new QHBoxLayout();
    _creation = creation;

    _title->setEnabled(creation);
    _mainLayout->addWidget(_title);
    if (!creation)
    {
        _btnClose = new QPushButton(tr("Close"));
        _btnClose->setObjectName("Close");
        _btnEdit = new QPushButton(tr("Edit"));
        _btnEdit->setObjectName("Edit");
        QObject::connect(_btnClose, SIGNAL(released()), this, SLOT(TriggerCloseIssue()));
        QObject::connect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerEditTitle()));
        _mainLayout->addWidget(_btnEdit);
        _mainLayout->addWidget(_btnClose);
    }

    this->setLayout(_mainLayout);

    //Design
    style = "QLineEdit{"
            "max-height: 50px;"
            "}"
            "QPushButton#Edit{"
            "background-color: #595959;"
            "color: #ffffff;"
            "border-radius: 2px;"
            "min-width : 75px;"
            "min-height : 40px;"
            "max-width : 75px;"
            "max-height : 40px;"
            "font-size: 15px;"
            "font-weight: bold;"
            "}"
            "QPushButton#Edit:hover{"
            "background-color: #bababa;"
            "}"
            "QPushButton#Close{"
            "background-color: #c0392b;"
            "color: #ffffff;"
            "border-radius: 2px;"
            "min-width : 75px;"
            "min-height : 40px;"
            "max-width : 75px;"
            "max-height : 40px;"
            "font-size: 15px;"
            "font-weight: bold;"
            "}"
            "QPushButton#Close:hover{"
            "background-color: #d36c63;"
            "}";
    this->setStyleSheet(style);
}

void BugViewTitleWidget::TriggerCloseIssue()
{
    //TODO : Link API
    emit OnIssueClosed(_bugID);
}

void BugViewTitleWidget::SetTitle(const QString &title)
{
    _title->setText(title);
}

void BugViewTitleWidget::SetBugID(const int bugId)
{
    _bugID = bugId;
}

void BugViewTitleWidget::TriggerEditTitle()
{
    _title->setEnabled(true);
    _btnEdit->setText(tr("Save"));
    QObject::disconnect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerEditTitle()));
    QObject::connect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerSaveTitle()));
}

void BugViewTitleWidget::TriggerSaveTitle()
{
    _title->setEnabled(false);
    if (_creation){
        //TODO : Link API
    }
    _btnEdit->setText(tr("Edit"));
    QObject::disconnect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerSaveTitle()));
    QObject::connect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerEditTitle()));
    //TODO : Link API
    emit OnTitleEdit(_bugID);
}

QString BugViewTitleWidget::GetTitle()
{
    return _title->text();
}
