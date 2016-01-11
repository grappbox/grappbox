#include "BugViewPreviewWidget.h"

BugViewPreviewWidget::BugViewPreviewWidget(bool isCreation, bool createPage, QWidget *parent) : QWidget(parent)
{
    QString style;
    QWidget *widTitleBar = new QWidget();
    QWidget *widStatusBar = new QWidget();
    _mainLayout = new QVBoxLayout();
    _titleBar = new QHBoxLayout();
    _statusBar = new QHBoxLayout();
    _avatar = new QLabel();
    _lblName = new QLabel(API::SDataManager::GetDataManager()->GetUserName() + " " + API::SDataManager::GetDataManager()->GetUserLastName());
    _lblDate = new QLabel(PH_BUGPREVIEWDATE + " " + FormatDateTime(QDateTime::currentDateTime()) + " by " + _lblName->text());
    _comment = new QTextEdit(isCreation ? tr("Enter the comment here") : "");
    _commentTitle = new QLineEdit(isCreation ? tr("Enter the comment title here") : "");

    _comment->setEnabled(isCreation);
    _commentTitle->setEnabled(isCreation);
    _titleBar->addWidget(_avatar);
    _titleBar->addWidget(_commentTitle);

    _statusBar->addWidget(_lblDate);

    if (!isCreation)
    {
        _btnEdit = new QPushButton(tr("Edit"));
        _statusBar->addWidget(_btnEdit);
        QObject::connect(_btnEdit, SIGNAL(released()), this, SLOT(TriggerEditBtnReleased()));
    }
    else
    {
        if (createPage)
            _btnComment = new QPushButton(tr("Comment and open"));
        else
            _btnComment = new QPushButton(tr("Comment"));
        _statusBar->addWidget(_btnComment);
        _bugID = -1;
        QObject::connect(_btnComment, SIGNAL(released()), this, SLOT(TriggerCommentBtnReleased()));
    }

    widTitleBar->setLayout(_titleBar);
    widStatusBar->setLayout(_statusBar);
    _mainLayout->addWidget(widTitleBar);
    _mainLayout->addWidget(_comment);
    _mainLayout->addWidget(widStatusBar);

    this->setLayout(_mainLayout);

    //Design
    widTitleBar->setObjectName("TitleBar");
    widStatusBar->setObjectName("StatusBar");
    _titleBar->setSpacing(0);
    _statusBar->setSpacing(0);
    _mainLayout->setMargin(0);
    _mainLayout->setSpacing(0);
    style = "QWidget#TitleBar{"
            "background-color: #c0392b;"
            "color: white;"
            "min-height: 40px;"
            "max-height: 40px;"
            "}"
            "QWidget#StatusBar{"
            "background-color: #3c3b3b;"
            "color: white;"
            "min-height: 40px;"
            "max-height: 40px;"
            "}"
            "QPushButton{"
            "min-height: 20px;"
            "max-height: 20px;"
            "min-width: 100px;"
            "max-width: 150px;"
            "top: -5px;"
            "}"
            "QLabel{"
            "color: white;"
            "}";
    this->setStyleSheet(style);
}

const QString &BugViewPreviewWidget::GetComment() const
{
    return _comment->toPlainText();
}

const QString &BugViewPreviewWidget::GetCommentTitle() const
{
    return _commentTitle->text();
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

void BugViewPreviewWidget::SetAvatar(const QPixmap &avatar)
{
    _avatar->setPixmap(avatar);
}

void BugViewPreviewWidget::SetComment(const QString &comment)
{
    _comment->setText(comment);
}

void BugViewPreviewWidget::SetCommentTitle(const QString &title)
{
    _commentTitle->setText(title);
}

QString BugViewPreviewWidget::FormatDateTime(const QDateTime &datetime)
{
    return datetime.toString("yyyy/MM/dd " + tr("at") + " hh:mm:ss");
}

void BugViewPreviewWidget::TriggerEditBtnReleased()
{
    _comment->setEnabled(!_comment->isEnabled());
    _commentTitle->setEnabled(!_commentTitle->isEnabled());
    if (_comment->isEnabled())
    {
        _btnEdit->setText(tr("Save"));
        emit OnEdit(_bugID);
    }
    else
    {
        _btnEdit->setText(tr("Edit"));
        emit OnSaved(_bugID);
    }
}

void BugViewPreviewWidget::TriggerCommentBtnReleased()
{
    emit OnCommented();
}
