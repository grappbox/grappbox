#include <QDebug>
#include <QVector>
#include <QJsonDocument>
#include <QJsonObject>
#include <QJsonValueRef>
#include <QMovie>
#include <QDateTime>
#include "utils.h"
#include "SDataManager.h"
#include "SFontLoader.h"
#include "MessageTimeLine.h"

MessageTimeLine::MessageTimeLine(MessageTimeLineInfo data, int IdTimeline, QWidget *parent) : QWidget(parent)
{
    _IDTimelineMessage = data.IdTimeline;
    _IDUserCreator = data.IdUser;
    _IDTimeline = IdTimeline;

    _MessageData = data;

    bool canEdit = _IDUserCreator == API::SDataManager::GetDataManager()->GetUserId();

    QWidget *normalModeWidget = new QWidget(this);
    QWidget *editModeWidget = new QWidget(this);

    _MainLayoutLoading = new QGridLayout();
    _MainLayout = new QStackedLayout();
    _MainLayoutNormal = new QGridLayout();
    _MainLayoutEdit = new QVBoxLayout();
    _ButtonLayout = new QHBoxLayout();

    _LoadingImage = new QLabel("Test");
    _LoadingImage->setStyleSheet("QLabel {background-color: #AAAAAA85;}");
    _LoadingImage->setAlignment(Qt::AlignCenter);
    QMovie *loading = new QMovie(":/icon/Ressources/Icon/Loading2.gif");
    _LoadingImage->setMovie(loading);
    loading->start();

    _MainLayoutLoading->addLayout(_MainLayout, 0, 0, 1, 1);
    _MainLayoutLoading->addWidget(_LoadingImage, 0, 0, 1, 1);

    _LoadingImage->hide();

    _Avatar = new QLabel();
    if (data.Avatar != nullptr)
        _Avatar->setPixmap(QPixmap::fromImage(*data.Avatar).scaled(QSize(64, 64)));
    else
        _Avatar->setText("Cannot retrieve avatar");
    _Avatar->setFixedSize(64, 64);
    _Title = new QLabel(data.Title);
    _Title->setFixedHeight(30);
    _Title->setStyleSheet("background: #2abb67; color: #FFFFFF; border-style:none; border-bottom-style: solid; border-width: 1px; border-color: #FFFFFF; ");
    QFont font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPixelSize(20);
    _Title->setFont(font);
    _Message = new QLabel(data.Message);
    _Message->setWordWrap(true);
    _Message->setStyleSheet("QLabel {border-style:none; border-bottom-style: solid; border-width: 1px; border-color: #5a5a5a; }");
    _Date = new QLabel("Last modified : " + data.DateLastModification.toString("dd/MM/yyyy hh:mm") + " by " + data.LastName + ' ' + data.Name);
    _Date->setFixedHeight(24);
    _EditButton = new PushButtonImage();
    _EditButton->SetImage(QPixmap(":/icon/Ressources/Icon/Edit.png"));
    _EditButton->setFixedSize(24, 24);
    _RemoveButton = new PushButtonImage();
    _RemoveButton->SetImage(QPixmap(":/icon/Ressources/Icon/Delete.png"));
    _RemoveButton->setFixedSize(24, 24);
    _EditButton->setDisabled(!canEdit);
    _RemoveButton->setDisabled(!canEdit);

    _TitleEdit = new QLineEdit();
    _EditMessageArea = new QTextEdit();
    _EditMessageArea->setPlaceholderText("Enter here the comment.");
    _ValidateButton = new PushButtonImage();
    _ValidateButton->SetImage(QPixmap(":/icon/Ressources/Icon/Accept.png"));
    _ValidateButton->setFixedSize(24, 24);
    _CancelButton = new PushButtonImage();
    _CancelButton->SetImage(QPixmap(":/icon/Ressources/Icon/Return.png"));
    _CancelButton->setFixedSize(24, 24);

    _ButtonLayout->addWidget(_ValidateButton);
    if (_IDTimelineMessage == -1)
        _CancelButton->hide();
    _ButtonLayout->addWidget(_CancelButton);
    _MainLayoutEdit->addWidget(_TitleEdit);
    if (data.IdParent != 0)
        _TitleEdit->hide();
    _MainLayoutEdit->addWidget(_EditMessageArea);
    _MainLayoutEdit->addLayout(_ButtonLayout);

    editModeWidget->setLayout(_MainLayoutEdit);
    if (data.IdParent <= 0)
        _MainLayoutNormal->addWidget(_Title, 0, 0, 1, 6);
    _MainLayoutNormal->addWidget(_Avatar, 1, 0, 3, 1);
    _MainLayoutNormal->addWidget(_Message, 1, 1, 2, 5);
    _MainLayoutNormal->addWidget(_Date, 3, 1, 1, 3);
    if (canEdit)
    {
        _MainLayoutNormal->addWidget(_EditButton, 3, 4, 1, 1);
        _MainLayoutNormal->addWidget(_RemoveButton, 3, 5, 1, 1);
    }
    _MainLayoutNormal->setSpacing(0);
    _MainLayoutNormal->setContentsMargins(0, 0, 0, 0);

    normalModeWidget->setLayout(_MainLayoutNormal);

    _IDLayoutNormal = _MainLayout->addWidget(normalModeWidget);
    _IDLayoutEdit = _MainLayout->addWidget(editModeWidget);
    if (_IDTimelineMessage == -1)
        _MainLayout->setCurrentIndex(_IDLayoutEdit);

    setLayout(_MainLayoutLoading);
    setStyleSheet("background-color: #FFFFFF;");
    setContentsMargins(0, 0, 0, 0);

    QObject::connect(_EditButton, SIGNAL(clicked(bool)), this, SLOT(OnEdit()));
    QObject::connect(_RemoveButton, SIGNAL(clicked(bool)), this, SLOT(OnRemove()));
    QObject::connect(_CancelButton, SIGNAL(clicked(bool)), this, SLOT(OnCancelEdit()));
    QObject::connect(_ValidateButton, SIGNAL(clicked(bool)), this, SLOT(OnConfirmEdit()));
}

void MessageTimeLine::OnEdit()
{
    _TitleEdit->setText(_Title->text());
    _EditMessageArea->setText(_Message->text());
    _MainLayout->setCurrentIndex(_IDLayoutEdit);
}

void MessageTimeLine::OnRemove()
{
    QVector<QString> data;
    data.push_back(TO_STRING(USER_TOKEN));
    data.push_back(TO_STRING(_IDTimeline));
    data.push_back(TO_STRING(_IDTimelineMessage));
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_TIMELINE, API::GR_ARCHIVE_MESSAGE_TIMELINE, data, this, "OnDeleteDone", "OnDeleteFail");
}

void MessageTimeLine::OnCancelEdit()
{
    _MainLayout->setCurrentIndex(_IDLayoutNormal);
}

void MessageTimeLine::OnConfirmEdit()
{
    if (_IDTimelineMessage == -1)
    {
        QVector<QString> data;
        data.push_back(TO_STRING(_IDTimeline));
        data.push_back(TO_STRING(USER_TOKEN));
        data.push_back(_TitleEdit->text());
        data.push_back(_EditMessageArea->toPlainText());
        data.push_back(TO_STRING(_MessageData.IdParent));
        API::SDataManager::GetCurrentDataConnector()->Post(API::DP_TIMELINE, API::PR_MESSAGE_TIMELINE, data, this, "OnEditDone", "OnEditFail");
        this->setDisabled(true);
        return;
    }
    _LoadingImage->show();
    _EditButton->setDisabled(true);
    _RemoveButton->setDisabled(true);
    QVector<QString> data;
    data.push_back(TO_STRING(_IDTimeline));
    data.push_back(TO_STRING(USER_TOKEN));
    data.push_back(TO_STRING(_IDTimelineMessage));
    data.push_back(_TitleEdit->text());
    data.push_back(_EditMessageArea->toPlainText());
    qDebug() << "ConfirmEdit data set";
    API::SDataManager::GetCurrentDataConnector()->Post(API::DP_TIMELINE, API::PR_EDIT_MESSAGE_TIMELINE, data, this, "OnEditDone", "OnEditFail");
    qDebug() << "ConfirmEdit sent to API";
    _BeforeAPITitle = _Title->text();
    _BeforeAPIMessage = _Message->text();
    _Title->setText(_TitleEdit->text());
    _Message->setText(_EditMessageArea->toPlainText());
    if (_IDTimelineMessage != -1)
        _MainLayout->setCurrentIndex(_IDLayoutNormal);
}

void MessageTimeLine::OnEditDone(int id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    if (_IDTimelineMessage == -1)
    {
        QJsonObject obj = doc.object()["message"].toObject();
        if (!obj["deletedAt"].isNull())
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
        mtl.IdUser = obj["userId"].toInt();
        emit NewMessage(mtl);
        this->setDisabled(false);
        _EditMessageArea->setText("");
        return;
    }
    _LoadingImage->hide();
    _EditButton->setDisabled(false);
    _RemoveButton->setDisabled(false);
    _BeforeAPITitle = "";
    _BeforeAPIMessage = "";
    QJsonObject obj = doc.object()["message"].toObject();
    QString dateStr;
    QString format = "yyyy-MM-dd HH:mm:ss.zzzz";
    dateStr = obj["editedAt"].toObject()["date"].toString();
    QDateTime date = QDateTime::fromString(dateStr, format);
    _Date->setText("Last modified : " + date.toString("dd/MM/yyyy hh:mm") + " by " + _MessageData.LastName + ' ' + _MessageData.Name);
}

void MessageTimeLine::OnEditFail(int id, QByteArray data)
{
    _LoadingImage->hide();
    _EditButton->setDisabled(false);
    _RemoveButton->setDisabled(false);
    _Title->setText(_BeforeAPITitle);
    _Message->setText(_BeforeAPIMessage);
    qDebug() << "Unable to push informations.";
}

void MessageTimeLine::OnDeleteDone(int id, QByteArray data)
{
    qDebug() << "Delete me ! Summon TimelineDeleted";
    emit TimelineDeleted(_IDTimelineMessage);
}

void MessageTimeLine::OnDeleteFail(int id, QByteArray data)
{

}
