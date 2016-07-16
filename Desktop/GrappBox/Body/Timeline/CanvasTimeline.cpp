#include <QMessageBox>
#include <QJsonDocument>
#include <QJsonObject>
#include <QJsonValueRef>
#include <QPair>
#include <QJsonArray>
#include <QDebug>
#include "utils.h"
#include "SDataManager.h"
#include "SFontLoader.h"
#include "CanvasTimeline.h"

CanvasTimeline::CanvasTimeline(QWidget *parent) : QWidget(parent)
{
    _MainTimelineLayout = new QGridLayout();

    _MainTimelineLayout->setSpacing(0);
    _MainTimelineLayout->setContentsMargins(5, 5, 0, 0);

    _LoadMore = new QPushButton("Load more...");

    _ContenerAddMessage = new QWidget();
    QWidget *mainW = new QWidget();
    _ContenerAddMessage->setLayout(new QVBoxLayout());
    _ContenerAddMessage->layout()->setContentsMargins(100, 0, 100, 30);
    _ContenerAddMessage->layout()->addWidget(mainW);
    mainW->setStyleSheet("background: #FFFFFF");
    _LayoutAddMessage = new QVBoxLayout();
    _LabelAddMessage = new QLabel("Add a new message");
    _LabelAddMessage->setStyleSheet("background: #2abb67; color: #FFFFFF; border-style:none; border-bottom-style: solid; border-width: 1px; border-color: #FFFFFF; ");
    QFont font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPixelSize(20);
    _LabelAddMessage->setAlignment(Qt::AlignCenter);
    _LabelAddMessage->setFont(font);

    _TitleLabel = new QLabel("Title");
    _TitleLabel->setStyleSheet("color: #424242; border-style:none; border-bottom-style:solid; border-width: 2px; border-color: #E0E0E0;");
    _TitleMessage = new QLineEdit();

    _MessageLabel = new QLabel("Message");
    _MessageLabel->setStyleSheet("color: #424242; border-style:none; border-bottom-style:solid; border-width: 2px; border-color: #E0E0E0;");
    _Message = new QTextEdit();
    _ConfirmAddingMessage = new QPushButton("Add");
    _ConfirmAddingMessage->setStyleSheet("QPushButton { "
                                         "background: #2abb67;"
                                         "color: #FFFFFF; "
                                         "border-style:none; "
                                         "border-bottom-style: solid; "
                                         "border-width: 1px; "
                                         "border-color: #FFFFFF;}"
                                         "QPushButton:hover {background: #1aab57;}"
                                         "QPushButton:pressed {background: #3acb77;}");
    _ConfirmAddingMessage->setFont(font);
    _LayoutAddMessage->addWidget(_LabelAddMessage);

    _LayoutAddMessage->addWidget(_TitleLabel);
    _LayoutAddMessage->addWidget(_TitleMessage);
    _LayoutAddMessage->addWidget(_MessageLabel);
    _LayoutAddMessage->addWidget(_Message);
    _LayoutAddMessage->addWidget(_ConfirmAddingMessage);

    _LayoutAddMessage->setContentsMargins(0, 0, 0, 0);
    _LayoutAddMessage->setSpacing(1);

    mainW->setLayout(_LayoutAddMessage);

    _MainTimelineLayout->addWidget(_ContenerAddMessage, 0, 0, 1, 3);

    setLayout(_MainTimelineLayout);

    QObject::connect(_LoadMore, SIGNAL(clicked(bool)), this, SLOT(OnLoadMore()));
    QObject::connect(_ConfirmAddingMessage, SIGNAL(clicked(bool)), this, SLOT(AddingTimeline()));
}

void CanvasTimeline::TimelineGetUserDone(int id, QByteArray array)
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

void CanvasTimeline::TimelineGetUserFailed(int id, QByteArray array)
{

}

void CanvasTimeline::TimelineAddMessageDone(int id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    if (!obj["deleted_at"].isNull())
        return;
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
    if (!_Messages.contains(mtl))
        _Messages.append(mtl);
    bool HaveToRetrieveUser = true;
    for (API::UserInformation user : _Users)
    {
        if (user.id == USER_ID)
        {
            HaveToRetrieveUser = false;
            break;
        }
    }
    if (HaveToRetrieveUser)
    {
        API::UserInformation userInfo;
        userInfo.id = USER_ID;
        BEGIN_REQUEST;
        {
            SET_CALL_OBJECT(this);
            SET_ON_DONE("TimelineGetUserDone");
            SET_ON_FAIL("TimelineGetUserFailed");
            ADD_URL_FIELD(USER_TOKEN);
            ADD_URL_FIELD(USER_ID);
            int requestId = GET(API::DP_USER_DATA, API::GR_USER_DATA);
            _Users[requestId] = userInfo;
        }
        END_REQUEST;
        return;
    }
    FinishedLoad();
}

void CanvasTimeline::TimelineAddMessageFailed(int id, QByteArray array)
{

}

void CanvasTimeline::FinishedLoad()
{
    while (QLayoutItem *item = _MainTimelineLayout->takeAt(0))
    {
        QWidget *widget = item->widget();
        if (widget != nullptr && widget != _ContenerAddMessage && widget != _LoadMore)
            delete widget;
        delete item;
    }
    _MainTimelineLayout->addWidget(_ContenerAddMessage, 0, 0, 1, 3);
    _Conversation.clear();
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
        ConversationTimeline *c = new ConversationTimeline(_IDTimeline, info, this);
        _Conversation.append(c);
        int i = _MainTimelineLayout->count() / 3;
        _MainTimelineLayout->addWidget(c, i + 1, (i % 2) * 2, 1, 1);
        _MainTimelineLayout->addWidget(new LineTimeline(), i + 1, 1, 1, 1);
        _MainTimelineLayout->addWidget(new QLabel(""), i + 1, ((i + 1) % 2) * 2, 1, 1);
        QObject::connect(c, SIGNAL(OnDeleteMainMessage(int)), this, SLOT(DeleteMessage(int)));
        QObject::connect(c, SIGNAL(AnimOpenComment(int)), this, SLOT(UpdateTimelineAnim(int)));
    }
    _TotalLoad = _Messages.size();
    _MainTimelineLayout->addWidget(_LoadMore, _MainTimelineLayout->count() / 3 + 1, 0, 1, 3);
    _MainTimelineLayout->setColumnStretch(0, 10);
    _MainTimelineLayout->setColumnStretch(1, 1);
    _MainTimelineLayout->setColumnStretch(2, 10);
    emit OnFinishedLoading(_IDTimeline);
}

void CanvasTimeline::OnLoadMore()
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("TimelineGetDone");
        SET_ON_FAIL("TimelineGetFailed");
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(_IDTimeline);
        ADD_URL_FIELD(_TotalLoad);
        ADD_URL_FIELD(10);
        GET(API::DP_TIMELINE, API::GR_TIMELINE);
    }
    END_REQUEST;
}

void CanvasTimeline::TimelineGetDone(int id, QByteArray array)
{
    QList<int> userIdToRetrieve;
    QJsonDocument doc = QJsonDocument::fromJson(array);
    QJsonObject objMain = doc.object()["data"].toObject();
    qDebug() << objMain;
    for (QJsonValueRef ref : objMain["array"].toArray())
    {
        QJsonObject obj = ref.toObject();
        if (!obj["deleted_at"].isNull())
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
        if (!_Messages.contains(mtl))
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
            SET_ON_FAIL("TimelineGetUserFail");
            ADD_URL_FIELD(USER_TOKEN);
            ADD_URL_FIELD(i);
            int requestId = GET(API::DP_USER_DATA, API::GR_USER_DATA);
            _Users[requestId] = userInfo;
        }
        END_REQUEST;
    }
}

void CanvasTimeline::TimelineGetFailed(int id, QByteArray array)
{

}

void CanvasTimeline::LoadData(int id)
{
    _Messages.clear();
    _Users.clear();
    _IDTimeline = id;

    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("TimelineGetDone");
        SET_ON_FAIL("TimelineGetFailed");
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(id);
        ADD_URL_FIELD(0);
        ADD_URL_FIELD(10);
        GET(API::DP_TIMELINE, API::GR_TIMELINE);
    }
    END_REQUEST;
}

void  CanvasTimeline::UpdateTimelineAnim(int Id)
{
    for (ConversationTimeline *item : _Conversation)
    {
        if (item->GetID() != Id)
            item->ForceCloseComment();
    }
}

void CanvasTimeline::AddingTimeline()
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("TimelineAddMessageDone");
        SET_ON_FAIL("TimelineAddMessageFailed");
        ADD_URL_FIELD(_IDTimeline);
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("title", _TitleMessage->text());
        ADD_FIELD("message", _Message->toPlainText());
        POST(API::DP_TIMELINE, API::PR_MESSAGE_TIMELINE);
    }
    END_REQUEST;
    _TitleMessage->setText("");
    _Message->setText("");
}

void CanvasTimeline::DeleteMessage(int id)
{
    qDebug() << "Delete message : " << id;
    for (MessageTimeLine::MessageTimeLineInfo item : _Messages)
    {
        if (item.IdTimeline == id)
        {
            _Messages.removeOne(item);
            break;
        }
    }
    FinishedLoad();
}
