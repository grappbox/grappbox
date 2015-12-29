#include "ConversationTimeline.h"
#include <QPropertyAnimation>

ConversationTimeline::ConversationTimeline(int id, bool revert, QWidget *parent) : QWidget(parent)
{
    _ConversationId = id;
    _MainLayout = new QVBoxLayout();
    _CommentLayout = new QVBoxLayout();

    _OpenComment = new QPushButton("View comments");
    _OpenComment->setIcon(QIcon(":/icon/Ressources/Icon/DropDown.png"));
    _OpenComment->setStyleSheet("background: #FFFFFF; border-style: none; border-top-style: solid; border-bottom-style: solid; border-width: 1px; border-color: #7f8c8d;");

    _MainCommentWidget = new QWidget(this);
    _ScrollAnim = new QScrollArea();
    _ScrollAnim->setHorizontalScrollBarPolicy( Qt::ScrollBarAlwaysOff );
    _ScrollAnim->setVerticalScrollBarPolicy( Qt::ScrollBarAlwaysOff );

    _MainMessage = new MessageTimeLine(1);
    _CommentLayout->addWidget(_MainMessage);
    _CommentLayout->addWidget(new MessageTimeLine(-1));
    for (int i = 0; i < 2; ++i)
    {
        _CommentMessage[i + 2] = new MessageTimeLine(i + 2);
        _CommentLayout->addWidget(_CommentMessage[i + 2]);
    }

    _MainCommentWidget->setLayout(_CommentLayout);
    _CommentLayout->setContentsMargins(20, 0, 0, 0);
    _ScrollAnim->setWidget(_MainCommentWidget);
    _ScrollAnim->setMinimumHeight(0);
    _ScrollAnim->setMaximumHeight(0);
    _ScrollAnim->setWidgetResizable(true);
    _ScrollAnim->setStyleSheet("QScrollArea {border-style:none;}");

    _MainLayout->addWidget(_MainMessage);
    _MainLayout->addWidget(_OpenComment);
    _MainLayout->addWidget(_ScrollAnim);

    setLayout(_MainLayout);
    _MainLayout->setSpacing(0);
    _MainLayout->setContentsMargins(0, 0, 0, 0);
    _CommentLayout->setSpacing(0);

    _CommentHide = true;

    QObject::connect(_OpenComment, SIGNAL(clicked(bool)), this, SLOT(OpenComment()));
}

int ConversationTimeline::GetID() const
{
    return _ConversationId;
}

void ConversationTimeline::OpenComment()
{
    _MainMessage->setFixedSize(_MainMessage->geometry().size());
    int realSize = _MainCommentWidget->height();
    _CommentHide = !_CommentHide;
    QPropertyAnimation *animationMax = new QPropertyAnimation(_ScrollAnim, "maximumHeight");
    QPropertyAnimation *animationMin = new QPropertyAnimation(_ScrollAnim, "minimumHeight");
    animationMax->setDuration(500);
    animationMax->setStartValue(QVariant(_CommentHide ? realSize : 0));
    animationMax->setEndValue(QVariant(!_CommentHide ? realSize : 0));
    animationMax->start();
    animationMin->setDuration(500);
    animationMin->setStartValue(QVariant(_CommentHide ? realSize : 0));
    animationMin->setEndValue(QVariant(!_CommentHide ? realSize : 0));
    animationMin->start();
    if (_CommentHide)
    {
        _OpenComment->setIcon(QIcon(":/icon/Ressources/Icon/DropDown.png"));
        _OpenComment->setStyleSheet("background: #FFFFFF; border-style: none; border-top-style: solid; border-bottom-style: solid; border-width: 1px; border-color: #7f8c8d;");
        _OpenComment->setText("Show comments");
    }
    else
    {
        QTransform trans = QTransform().rotate(180);
        QPixmap img = QPixmap(":/icon/Ressources/Icon/DropDown.png").transformed(trans);
        _OpenComment->setIcon(QIcon(img));
        _OpenComment->setStyleSheet("background: #FFFFFF; border-style: none; border-top-style: solid; border-bottom-style: solid; border-width: 1px; border-color: #2980b9;");
        _OpenComment->setText("Hide comments");
    }
    if (!_CommentHide)
        emit AnimOpenComment(_ConversationId);
}

void ConversationTimeline::ForceCloseComment()
{
    if (!_CommentHide)
        OpenComment();
}

void ConversationTimeline::TimeLineDelete(int)
{

}

void ConversationTimeline::TimeLineEdit(int)
{

}
