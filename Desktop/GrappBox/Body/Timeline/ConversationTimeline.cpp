#include "ConversationTimeline.h"
#include <QPropertyAnimation>
#include <QJsonDocument>
#include <QJsonObject>
#include <QJsonValueRef>
#include <QPair>
#include <QJsonArray>
#include <QMessageBox>
#include <QDebug>
#include "utils.h"

ConversationTimeline::ConversationTimeline(int timelineId, MessageTimeLine::MessageTimeLineInfo data, bool revert, QWidget *parent) : QWidget(parent)
{
    _TimelineId = timelineId;
    _ConversationId = data.IdTimeline;
    _MainLayout = new QVBoxLayout();
    _CommentLayout = new QVBoxLayout();

    _OpenComment = new QPushButton("View comments");
    _OpenComment->setIcon(QIcon(":/icon/Ressources/Icon/DropDown.png"));
    _OpenComment->setStyleSheet("background: #FFFFFF; border-style: none; border-top-style: solid; border-bottom-style: solid; border-width: 1px; border-color: #7f8c8d;");

    _MainCommentWidget = new QWidget();
    _ScrollAnim = new QScrollArea();
    _ScrollAnim->setHorizontalScrollBarPolicy( Qt::ScrollBarAlwaysOff );
    _ScrollAnim->setVerticalScrollBarPolicy( Qt::ScrollBarAlwaysOff );

    _MainMessage = new MessageTimeLine(data, timelineId);
    _CommentMessageNew = new MessageTimeLine(MessageTimeLine::MessageTimeLineInfo(-1, data.IdTimeline, "", "", QDateTime(), 1, nullptr, "", ""), timelineId);

    _CommentLayout->addWidget(_CommentMessageNew);
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
    _ReloadContain = false;

    QObject::connect(_MainMessage, SIGNAL(TimelineDeleted(int)), this, SLOT(TimeLineDelete(int)));
    QObject::connect(_CommentMessageNew, SIGNAL(NewMessage(MessageTimeLine::MessageTimeLineInfo)), this, SLOT(NewComment(MessageTimeLine::MessageTimeLineInfo)));
    QObject::connect(_OpenComment, SIGNAL(clicked(bool)), this, SLOT(OpenComment()));
}

void ConversationTimeline::update()
{
    QWidget::update();
    _ScrollAnim->setMaximumHeight(_MainCommentWidget->height());
}

int ConversationTimeline::GetID() const
{
    return _ConversationId;
}

void ConversationTimeline::OnAnimEnd()
{
    _OpenComment->setDisabled(false);
}

void ConversationTimeline::NewComment(MessageTimeLine::MessageTimeLineInfo info)
{
    _ReloadContain = true;
    OpenComment();
}

void ConversationTimeline::OpenCommentAnim()
{
    int realSize = _MainCommentWidget->minimumSizeHint().height();
    if (_ReloadContain)
    {
        _ScrollAnim->setMinimumHeight(realSize);
        _ScrollAnim->setMaximumHeight(realSize);
        _ReloadContain = false;
        _OpenComment->setDisabled(false);
        return;
    }
    _CommentHide = !_CommentHide;
    _ScrollAnim->setHidden(_CommentHide);
    _MainMessage->setFixedHeight(_MainMessage->height());
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
    QObject::connect(animationMax, SIGNAL(finished()), this, SLOT(OnAnimEnd()));
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

void ConversationTimeline::OpenComment()
{
    if (!_CommentHide && !_ReloadContain)
    {
        OpenCommentAnim();
        return;
    }
	BEGIN_REQUEST;
	{
		SET_CALL_OBJECT(this);
		SET_ON_DONE("TimelineGetDone");
		SET_ON_FAIL("TimelineGetFailed");
		ADD_URL_FIELD(USER_TOKEN);
		ADD_URL_FIELD(_TimelineId);
		ADD_URL_FIELD(_ConversationId);
		GET(API::DP_TIMELINE, API::GR_COMMENT_TIMELINE);
	}
	END_REQUEST;
    _OpenComment->setDisabled(true);
}

void ConversationTimeline::TimelineGetUserDone(int id, QByteArray array)
{
    QJsonDocument doc = QJsonDocument::fromJson(array);
    QJsonObject objMain = doc.object()["data"].toObject();
    _Users[id].firstName = objMain["firstname"].toString();
    _Users[id].lastName = objMain["lastname"].toString();
    _Users[id].email = objMain["email"].toString();
    _Users[id].avatar = QImage::fromData(QByteArray::fromBase64(objMain["avatar"].toString().toStdString().c_str()), "PNG");
    for (API::UserInformation user : _Users)
    {
        if (user.email == "")
            return;
    }
    FinishedLoad();
}

void ConversationTimeline::TimelineGetUserFailed(int id, QByteArray array)
{

}

void ConversationTimeline::FinishedLoad()
{
    while (QLayoutItem* item = _CommentLayout->takeAt(0))
    {
        QWidget* widget = item->widget();
        if (widget != nullptr && widget != _CommentMessageNew)
            delete widget;
        delete item;
    }
    _CommentLayout->addWidget(_CommentMessageNew);
	_CommentMessageNew->setSizePolicy(QSizePolicy::MinimumExpanding, QSizePolicy::MinimumExpanding);
    for (MessageTimeLine::MessageTimeLineInfo info : _Messages)
    {
        for (API::UserInformation user : _Users)
        {
            if (user.id == info.IdUser)
            {
                info.Name = user.firstName;
                info.LastName = user.lastName;
                info.Avatar = new QImage(user.avatar);
            }
        }
        MessageTimeLine *c = new MessageTimeLine(info, _TimelineId, this);
		c->setSizePolicy(QSizePolicy::MinimumExpanding, QSizePolicy::MinimumExpanding);
        _CommentMessage[info.IdTimeline] = c;
        _CommentLayout->addWidget(c);
        QObject::connect(c, SIGNAL(TimelineDeleted(int)), this, SLOT(TimeLineDelete(int)));
    }
    _MainCommentWidget->setLayout(_CommentLayout);
    QTimer *time = new QTimer();
    time->setSingleShot(true);
    QObject::connect(time, SIGNAL(timeout()), this, SLOT(OpenCommentAnim()));
    time->start(100);
}

void ConversationTimeline::TimelineGetDone(int id, QByteArray array)
{
    QList<int> userIdToRetrieve;
    QJsonDocument doc = QJsonDocument::fromJson(array);
    QJsonObject objMain = doc.object()["data"].toObject();
    for (QJsonValueRef ref : objMain["array"].toArray())
    {
        QJsonObject obj = ref.toObject();
        if (!obj["deletedAt"].isNull())
            continue;
        MessageTimeLine::MessageTimeLineInfo mtl;
        mtl.IdTimeline = obj["id"].toInt();
        mtl.IdParent = obj["parentId"].toInt();
        mtl.Title = obj["title"].toString();
        mtl.Message = obj["message"].toString();
        QDateTime date;
        QString dateStr;
        QString format = "yyyy-MM-dd HH:mm:ss.zzzz";
        if (obj["editedAt"].isNull())
            dateStr = obj["createdAt"].toObject()["date"].toString();
        else
            dateStr = obj["editedAt"].toObject()["date"].toString();
        date = QDateTime::fromString(dateStr, format);
        mtl.DateLastModification = date;
        mtl.IdUser = obj["creator"].toObject()["id"].toInt();
        if (!userIdToRetrieve.contains(mtl.IdUser))
            userIdToRetrieve.append(mtl.IdUser);
        bool haveToHadMessage = true;
        for (MessageTimeLine::MessageTimeLineInfo mess : _Messages)
        {
            if (mess.IdTimeline == mtl.IdTimeline)
            {
                haveToHadMessage = false;
                break;
            }
        }
        if (haveToHadMessage)
            _Messages.append(mtl);
    }
    if (userIdToRetrieve.size() == 0)
    {
        FinishedLoad();
        return;
    }
    for (int i : userIdToRetrieve)
    {
        API::UserInformation userInfo;
        userInfo.id = i;
        BEGIN_REQUEST;
        {
            SET_CALL_OBJECT(this);
            SET_ON_DONE("TimelineGetUserDone");
            SET_ON_FAIL("TimelineGetUserFailed");
            ADD_URL_FIELD(USER_TOKEN);
            ADD_URL_FIELD(i);
            int requestId = GET(API::DP_USER_DATA, API::GR_USER_DATA);
            _Users[requestId] = userInfo;
        }
        END_REQUEST;
    }
}

void ConversationTimeline::TimelineGetFailed(int id, QByteArray array)
{

}

void ConversationTimeline::ForceCloseComment()
{
    if (!_CommentHide)
        OpenComment();
}

void ConversationTimeline::TimeLineDelete(int id)
{
    qDebug() << "Receive timeline delete from " << id;
    qDebug() << "Is main message : " << (id == _ConversationId);
    if (id == _ConversationId)
        emit OnDeleteMainMessage(_ConversationId);
    else
    {
        for (MessageTimeLine::MessageTimeLineInfo item : _Messages)
        {
            if (item.IdTimeline == id)
                _Messages.removeOne(item);
        }
        _ReloadContain = true;
        OpenComment();
    }
}
