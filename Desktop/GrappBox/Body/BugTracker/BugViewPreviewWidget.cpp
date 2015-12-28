#include "BugViewPreviewWidget.h"

BugViewPreviewWidget::BugViewPreviewWidget(bool isCreation, QWidget *parent) : QWidget(parent)
{
    _mainLayout = new QVBoxLayout();
    _titleBar = new QHBoxLayout();
    _statusBar = new QHBoxLayout();
    _avatar = new QPixmap();
    _lblName = new QLabel("");
    _lblDate = new QLabel(PH_BUGPREVIEWDATE + " " + FormatDateTime(QDateTime::currentDateTime()));
    _comment = new QTextEdit(isCreation ? tr("Enter the comment here") : "");

    //_titleBar->addWidget(_avatar);
    _titleBar->addWidget(_lblName);

    _statusBar->addWidget(_lblDate);

    if (!isCreation)
    {
        _btnEdit = new QPushButton(tr("Edit"));
        _titleBar->addWidget(_btnEdit);
        QObject::connect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerEditBtnReleased()));
    }
    else
    {
        _btnComment = new QPushButton(tr("Comment"));
        _statusBar->addWidget(_btnComment);
        _bugID = -1;
        QObject::connect(_btnComment, SIGNAL(released()), this, SLOT(TriggerCommentBtnReleased()));
    }

    //_mainLayout->addLayout(_titlebar);
    _mainLayout->addWidget(_comment);
    _mainLayout->addLayout(_statusBar);
    this->setLayout(_mainLayout);
}

void BugViewPreviewWidget::SetDate(const QDateTime &date)
{
    _lblDate->setText(PH_BUGPREVIEWDATE + " " + FormatDateTime(date));
}

void BugViewPreviewWidget::SetCommentor(const QString &name)
{
    _lblName->setText(name);
}

void BugViewPreviewWidget::SetID(const int id)
{
    _bugID = id;
}

QString BugViewPreviewWidget::FormatDateTime(const QDateTime &datetime)
{
    return datetime.toString("yyyy/MM/dd " + tr("at") + " hh:mm:ss");
}

void BugViewPreviewWidget::TriggerEditBtnReleased()
{
    _comment->setEnabled(!_comment->isEnabled());
    if (_comment->isEnabled())
    {
        _btnEdit->setText(tr("Save"));
        emit OnEdit(_bugID);
    }
    else
    {
        _btnEdit->setText(tr("Edit"));
        //TODO: Link API
        emit OnSaved(_bugID);
    }
}

void BugViewPreviewWidget::TriggerCommentBtnReleased()
{
    //TODO : LinkAPI
    emit OnCommented();
}
