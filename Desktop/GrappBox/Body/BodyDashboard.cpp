#include <QDebug>
#include <QtWidgets/QScrollArea>
#include <QJsonDocument>
#include <QJsonObject>
#include <QDateTime>

#include "SDataManager.h"
#include "SFontLoader.h"
#include "BodyDashboard.h"

using namespace DashboardInformation;

BodyDashboard::BodyDashboard(QWidget *parent) : QWidget(parent)
{
    _IsInitializing = false;

    _MainLayoutLoaded = new QVBoxLayout();
    _MemberAvaible = new QHBoxLayout();
    _NextMeeting = new QHBoxLayout();
    _GlobalProgress = new QHBoxLayout();

    setLayout(_MainLayoutLoaded);

    setSizePolicy(QSizePolicy::Expanding, QSizePolicy::Expanding);
}

void BodyDashboard::UpdateLayout()
{
    QFont font;
    font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPointSize(20);
    QString titleStyleSheet = "QLabel { color: #af2d2e;}";
    _TitleMemberAvaible = new QLabel("Your team occupation");
    _TitleMemberAvaible->setFont(font);
    _TitleMemberAvaible->setStyleSheet(titleStyleSheet);
    _TitleNextMeeting = new QLabel("Your next meetings");
    _TitleNextMeeting->setFont(font);
    _TitleNextMeeting->setStyleSheet(titleStyleSheet);
    _TitleGlobalProgress = new QLabel("Global progress");
    _TitleGlobalProgress->setFont(font);
    _TitleGlobalProgress->setStyleSheet(titleStyleSheet);

    QWidget *TopWidget;
    if (_MemberAvaible->count() == 0)
    {
        TopWidget = new QLabel("No member to manage.");
        QLabel *lab = dynamic_cast<QLabel*>(TopWidget);
        lab->setAlignment(Qt::AlignCenter);
        lab->setFont(font);
        lab->setStyleSheet(titleStyleSheet);
    }
    else
    {
        TopWidget = new QWidget();
        QVBoxLayout *TopLayout = new QVBoxLayout();
        QWidget *MemberWidget = new QWidget();
        MemberWidget->setObjectName("MemberWidget");
        MemberWidget->setStyleSheet("QWidget#MemberWidget { background: transparent;}");
        MemberWidget->setLayout(_MemberAvaible);
        _ScrollMember = new QScrollArea();
        _ScrollMember->setWidget(MemberWidget);
        _ScrollMember->setFixedHeight(_MemberAvaible->geometry().height() + 20);
        _ScrollMember->setStyleSheet("QScrollArea {border: none; background: transparent;}");

        TopLayout->addWidget(_TitleMemberAvaible);
        TopLayout->addWidget(_ScrollMember);
        TopWidget->setLayout(TopLayout);
    }

    QWidget *MiddleWidget;
    if (_NextMeeting->count() == 0)
    {
        MiddleWidget = new QLabel("No next meeting for now.");
        QLabel *lab = dynamic_cast<QLabel*>(MiddleWidget);
        lab->setAlignment(Qt::AlignCenter);
        lab->setFont(font);
        lab->setStyleSheet(titleStyleSheet);
    }
    else
    {
        MiddleWidget = new QWidget();
        MiddleWidget->setObjectName("MiddleWidget");
        MiddleWidget->setStyleSheet("QWidget#MiddleWidget { background: #f0f0f0; }");
        QVBoxLayout *MiddleLayout = new QVBoxLayout();
        QWidget *NextMeetingWidget = new QWidget();
        NextMeetingWidget->setObjectName("MemberWidget");
        NextMeetingWidget->setStyleSheet("QWidget#MemberWidget { background: transparent;}");
        NextMeetingWidget->setLayout(_NextMeeting);
        QScrollArea *scrollMeeting = new QScrollArea();
        scrollMeeting->setWidget(NextMeetingWidget);
        scrollMeeting->setFixedHeight(_NextMeeting->geometry().height() + 20);
        scrollMeeting->setStyleSheet("QScrollArea {border: none; background: transparent;}");
        MiddleLayout->addWidget(_TitleNextMeeting);
        MiddleLayout->addWidget(scrollMeeting);
        MiddleWidget->setLayout(MiddleLayout);
    }

    QWidget *BottomWidget;
    if (_GlobalProgress->count() == 0)
    {
        BottomWidget = new QLabel("No project associated.");
        QLabel *lab = dynamic_cast<QLabel*>(BottomWidget);
        lab->setAlignment(Qt::AlignCenter);
        lab->setFont(font);
        lab->setStyleSheet(titleStyleSheet);
    }
    else
    {
        BottomWidget = new QWidget();
        QVBoxLayout *BottomLayout = new QVBoxLayout();
        QWidget *GlobalProgressWidget = new QWidget();
        GlobalProgressWidget->setObjectName("MemberWidget");
        GlobalProgressWidget->setStyleSheet("QWidget#MemberWidget { background: transparent;}");
        GlobalProgressWidget->setLayout(_GlobalProgress);
        _ScrollProgress = new QScrollArea();
        _ScrollProgress->setWidget(GlobalProgressWidget);
        _ScrollProgress->setFixedHeight(_GlobalProgress->geometry().height() + 20);
        _ScrollProgress->setStyleSheet("QScrollArea {border: none; background: transparent;}");
        BottomLayout->addWidget(_TitleGlobalProgress);
        BottomLayout->addWidget(_ScrollProgress);
        BottomWidget->setLayout(BottomLayout);
    }

    _MainLayoutLoaded->addWidget(TopWidget, 5);
    _MainLayoutLoaded->addWidget(MiddleWidget, 5);
    _MainLayoutLoaded->addWidget(BottomWidget, 5);

    emit OnLoadingDone();
}

void BodyDashboard::Show(int ID, MainWindow *mainApp)
{
    _ProjectId = ID;
    _MainApplication = mainApp;
    show();

    if (_IsInitializing)
        return;
    _IsInitializing = true;
    _NumberBeforeInitializingDone = 3;

    QVector<QString> data;
    data.push_back(API::SDataManager::GetDataManager()->GetToken());
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_PROJECT, API::GR_LIST_PROJECT, data, this, "GetProjectsId", "Failure");
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_PROJECT, API::GR_LIST_MEMBER_PROJECT, data, this, "GetMemberProject", "Failure");
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_PROJECT, API::GR_LIST_MEETING, data, this, "GetNextMeeting", "Failure");
}

void BodyDashboard::Hide()
{
    hide();
}

void BodyDashboard::Failure(int id, QByteArray byte)
{
    QMessageBox::critical(this, "Connexion Error", "Failure to retreive data from internet");
    qDebug() << "FAILURE : " << byte;

}

void BodyDashboard::GetNextMeeting(int, QByteArray byte)
{
    _NumberBeforeInitializingDone--;
    QJsonDocument doc = QJsonDocument::fromJson(byte);
    QJsonObject objmain = doc.object();
    for (QJsonValueRef ref : objmain)
    {
        QJsonObject obj = ref.toObject();
        NextMeetingInfo *info = new NextMeetingInfo(NextMeetingInfo::Personnal, "", "", "", NULL);
        info->MeetingName = obj["event_title"].toString();
        QDateTime dateTime(QDateTime::fromString(obj["event_begin_date"].toObject()["date"].toString(), "yyyy-MM-dd hh:mm:ss"));
        info->Date = dateTime.toString("yyyy-MM-dd");
        info->Hours = dateTime.toString("hh:mm");
        info->ProjectIcon = new QPixmap(QPixmap::fromImage(QImage(":/Image/Ressources/Icon/ProjectDefault.png")));
        _NextMeeting->addWidget(new DashboardMeeting(info, this));
    }
    if (_NumberBeforeInitializingDone == 0)
       UpdateLayout();
    qDebug() << ">>>> NextMeeting " << _NumberBeforeInitializingDone;
}

void BodyDashboard::GetMemberProject(int, QByteArray byte)
{
    _NumberBeforeInitializingDone--;
    QJsonDocument doc = QJsonDocument::fromJson(byte);
    QJsonObject objmain = doc.object();
    for (QJsonValueRef ref : objmain)
    {
        QJsonObject obj = ref.toObject();
        MemberAvaiableInfo *info = new MemberAvaiableInfo("", false, NULL);
        info->MemberName = obj["first_name"].toString() + QString(" ") + obj["last_name"].toString();
        info->IsBusy = obj["occupation"].toBool();
        info->MemberPicture = new QPixmap(QPixmap::fromImage(QImage(":/Image/Ressources/Icon/UserDefault.png")));
        _MemberAvaible->addWidget(new DashboardMember(info, this));

    }
    if (_NumberBeforeInitializingDone == 0)
       UpdateLayout();
    qDebug() << ">>>> MemberProject " << _NumberBeforeInitializingDone;
}

void BodyDashboard::GetAllProject(int id, QByteArray byte)
{
    _NumberBeforeInitializingDone--;
    QJsonDocument doc = QJsonDocument::fromJson(byte);
    QJsonObject obj = doc.object();
    if (_ProjectIdsData.contains(id))
    {
        GlobalProgressInfo *currentInfo = new GlobalProgressInfo("", "", "", "", 0, 0, 0, 0);
        currentInfo->ProjectTitle = obj["name"].toString();
        currentInfo->ProjectMail = obj["contact_mail"].toString();
        currentInfo->ProjectPicture = new QPixmap(QPixmap::fromImage(QImage(":/Image/Ressources/Icon/ProjectDefault.png")));
        _GlobalProgress->addWidget(new DashboardGlobalProgress(currentInfo, this));
    }
    else
        qDebug() << "ID not contained";
    if (_NumberBeforeInitializingDone == 0)
       UpdateLayout();
    qDebug() << ">>>> Get All Project " << _NumberBeforeInitializingDone;
}

void BodyDashboard::GetProjectsId(int id, QByteArray byte)
{
    _NumberBeforeInitializingDone--;
    QJsonDocument doc = QJsonDocument::fromJson(byte);
    QJsonObject obj = doc.object();
    for (QJsonObject::iterator it = obj.begin(); it != obj.end(); ++it)
    {
        _NumberBeforeInitializingDone ++;
        QJsonObject project = (*it).toObject();
        QVector<QString> data;
        data.push_back(API::SDataManager::GetDataManager()->GetToken());
        data.push_back(QString::number(project["project_id"].toInt()));
        int idRequest = API::SDataManager::GetCurrentDataConnector()->Get(API::DP_PROJECT, API::GR_PROJECT, data, this, "GetAllProject", "Failure");
        _ProjectIdsData[idRequest] = project["project_id"].toInt();
    }
}
