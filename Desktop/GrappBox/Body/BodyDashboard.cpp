#include <QDebug>
#include <QtWidgets/QScrollArea>
#include <QJsonDocument>
#include <QJsonObject>
#include <QDateTime>
#include <QStringList>

#include "SDataManager.h"
#include "SFontLoader.h"
#include "BodyDashboard.h"
#include "utils.h"

using namespace DashboardInformation;

BodyDashboard::BodyDashboard(QWidget *parent) : QWidget(parent)
{
    _IsInitializing = false;
    _IsInitialized = false;

    _MainLayoutLoaded = new QVBoxLayout();
    _MemberAvaible = new QHBoxLayout();
    _NextMeeting = new QHBoxLayout();
    _GlobalProgress = new QHBoxLayout();

    setLayout(_MainLayoutLoaded);

    _UserId = -1;
    setSizePolicy(QSizePolicy::Expanding, QSizePolicy::Expanding);
    UpdateLayout(false);
}

void BodyDashboard::UpdateLayout(bool sendSignal)
{
    QFont font;
    font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPointSize(20);
    QString titleStyleSheet = "QLabel { color: #af2d2e;}";
    if (_IsInitialized)
        DeleteLayout();
    if (_MemberAvaible->count() == 0)
    {
        _TopWidget = new QWidget();
        QLabel *lab = new QLabel("No member to manage.");
        lab->setAlignment(Qt::AlignCenter);
        lab->setStyleSheet(titleStyleSheet);
        lab->setFont(font);
        _TitleMemberAvaible = new QLabel("Your team occupation", _TopWidget);
        _TitleMemberAvaible->setFont(font);
        _TitleMemberAvaible->setStyleSheet(titleStyleSheet);
        _TitleMemberAvaible->setAlignment(Qt::AlignLeft | Qt::AlignTop);
        QVBoxLayout *TopLayout = new QVBoxLayout();
        TopLayout->addWidget(_TitleMemberAvaible);
        TopLayout->addWidget(lab);
        _TopWidget->setLayout(TopLayout);
    }
    else
    {
        _TopWidget = new QWidget();
        _TitleMemberAvaible = new QLabel("Your team occupation", _TopWidget);
        _TitleMemberAvaible->setFont(font);
        _TitleMemberAvaible->setStyleSheet(titleStyleSheet);
        _TitleMemberAvaible->setAlignment(Qt::AlignLeft | Qt::AlignTop);
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
        _TopWidget->setLayout(TopLayout);
    }

    if (_NextMeeting->count() == 0)
    {
        _MiddleWidget = new QWidget();
        _TitleNextMeeting = new QLabel("Your next meetings", _MiddleWidget);
        _TitleNextMeeting->setFont(font);
        _TitleNextMeeting->setStyleSheet(titleStyleSheet);
        QLabel *lab = new QLabel("No next meeting for now.");
        lab->setAlignment(Qt::AlignCenter);
        lab->setFont(font);
        lab->setObjectName("MiddleWidget");
        lab->setStyleSheet(titleStyleSheet + QString("QWidget#MiddleWidget { background: #f0f0f0; }"));
        QVBoxLayout *MiddleLayout = new QVBoxLayout();
        MiddleLayout->addWidget(_TitleNextMeeting);
        MiddleLayout->addWidget(lab);
        _MiddleWidget->setLayout(MiddleLayout);
    }
    else
    {
        _MiddleWidget = new QWidget();
        _TitleNextMeeting = new QLabel("Your next meetings", _MiddleWidget);
        _TitleNextMeeting->setFont(font);
        _TitleNextMeeting->setStyleSheet(titleStyleSheet);
        _MiddleWidget->setObjectName("MiddleWidget");
        _MiddleWidget->setStyleSheet("QWidget#MiddleWidget { background: #f0f0f0; }");
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
        _MiddleWidget->setLayout(MiddleLayout);
    }

    if (_GlobalProgress->count() == 0)
    {
        _BottomWidget = new QWidget();
        _TitleGlobalProgress = new QLabel("Global progress", _BottomWidget);
        _TitleGlobalProgress->setFont(font);
        _TitleGlobalProgress->setStyleSheet(titleStyleSheet);
        QLabel *lab = new QLabel("No project associated.");
        QVBoxLayout *BottomLayout = new QVBoxLayout();
        lab->setAlignment(Qt::AlignCenter);
        lab->setFont(font);
        lab->setStyleSheet(titleStyleSheet);
        BottomLayout->addWidget(_TitleGlobalProgress);
        BottomLayout->addWidget(lab);
        _BottomWidget->setLayout(BottomLayout);
    }
    else
    {
        _BottomWidget = new QWidget();
        _TitleGlobalProgress = new QLabel("Global progress", _BottomWidget);
        _TitleGlobalProgress->setFont(font);
        _TitleGlobalProgress->setStyleSheet(titleStyleSheet);
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
        _BottomWidget->setLayout(BottomLayout);
    }

    _MainLayoutLoaded->addWidget(_TopWidget, 5);
    _MainLayoutLoaded->addWidget(_MiddleWidget, 5);
    _MainLayoutLoaded->addWidget(_BottomWidget, 5);

    setLayout(_MainLayoutLoaded);
    _IsInitialized = true;
    if (sendSignal)
    {
        _IsInitializing = false;
    }
}

void BodyDashboard::DeleteContent()
{
    QLayoutItem *wItem;
    while ((wItem = _GlobalProgress->takeAt(0)) != nullptr)
    {
        if (wItem->widget())
            delete wItem->widget();
        delete wItem;
    }
    while ((wItem = _MemberAvaible->takeAt(0)) != nullptr)
    {
        if (wItem->widget())
            delete wItem->widget();
        delete wItem;
    }
    while ((wItem = _NextMeeting->takeAt(0)) != nullptr)
    {
        if (wItem->widget())
            delete wItem->widget();
        delete wItem;
    }
    _GlobalProgress = new QHBoxLayout();
    _MemberAvaible = new QHBoxLayout();
    _NextMeeting = new QHBoxLayout();
}

void BodyDashboard::DeleteLayout()
{
    _MainLayoutLoaded->removeWidget(_TopWidget);
    _MainLayoutLoaded->removeWidget(_MiddleWidget);
    _MainLayoutLoaded->removeWidget(_BottomWidget);
    delete _TopWidget;
    delete _MiddleWidget;
    delete _BottomWidget;
}

void BodyDashboard::Show(int ID, MainWindow *mainApp)
{
    qDebug() << _IsInitializing;
    emit OnLoadingDone(_UserId);
    if (_IsInitializing)
        return;
    if (_IsInitialized)
        DeleteContent();
    UpdateLayout();
    _UserId = ID;
    _MainApplication = mainApp;

    _IsInitializing = true;
    _NumberBeforeInitializingDone = 3;

	BEGIN_REQUEST;
	{
		SET_CALL_OBJECT(this);
		SET_ON_DONE("GetAllProject");
		SET_ON_FAIL("Failure");
		ADD_URL_FIELD(USER_TOKEN);
		GET(API::DP_PROJECT, API::GR_LIST_PROJECT);
	}
	END_REQUEST;

	BEGIN_REQUEST;
	{
		SET_CALL_OBJECT(this);
		SET_ON_DONE("GetMemberProject");
		SET_ON_FAIL("Failure");
		ADD_URL_FIELD(USER_TOKEN);
		GET(API::DP_PROJECT, API::GR_LIST_MEMBER_PROJECT);
	}
	END_REQUEST;

	BEGIN_REQUEST;
	{
		SET_CALL_OBJECT(this);
		SET_ON_DONE("GetNextMeeting");
		SET_ON_FAIL("Failure");
		ADD_URL_FIELD(USER_TOKEN);
		GET(API::DP_PROJECT, API::GR_LIST_MEETING);
	}
	END_REQUEST;
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
    QJsonObject objmain = doc.object()["data"].toObject();
    for (QJsonValueRef ref : objmain["array"].toArray())
    {
        QJsonObject obj = ref.toObject();
        NextMeetingInfo *info = new NextMeetingInfo(NextMeetingInfo::Personnal, "", "", "", nullptr);
        info->MeetingName = obj["title"].toString();
        QJsonObject date = obj["begin_date"].toObject();
        QStringList l = date["date"].toString().split(' ');
        info->Date = l.at(0);
        l = l.at(1).split(':');
        info->Hours = l.at(0) + QString(":") + l.at(1);
        info->ProjectIcon = new QPixmap(QPixmap::fromImage(QImage(":/Image/Ressources/Icon/ProjectDefault.png")));
        _NextMeeting->addWidget(new DashboardMeeting(info, this));
    }
    qDebug() << _NumberBeforeInitializingDone;
    if (_NumberBeforeInitializingDone == 0)
       UpdateLayout();
}

void BodyDashboard::GetMemberProject(int, QByteArray byte)
{
    _NumberBeforeInitializingDone--;
    QJsonDocument doc = QJsonDocument::fromJson(byte);
    QJsonObject objmain = doc.object()["data"].toObject();
    for (QJsonValueRef ref : objmain["array"].toArray())
    {
        QJsonObject obj = ref.toObject();
        int userId = obj["users"].toObject()["id"].toInt();
        bool exist = false;
        QLayoutItem *item;
        for (int i = 0; (item = _MemberAvaible->itemAt(i)) != nullptr; ++i)
        {
            if (item->widget())
            {
                DashboardMember *member = dynamic_cast<DashboardMember*>(item->widget());
                if (member != nullptr)
                {
                    if (member->GetMemberInfo()->Id == userId)
                    {
                        exist = true;
                        break;
                    }
                }
            }
        }
        if (exist)
            continue;
        MemberAvaiableInfo *info = new MemberAvaiableInfo("", false, nullptr);
        info->MemberName = obj["users"].toObject()["firstname"].toString() + QString(" ") + obj["users"].toObject()["lastname"].toString();
        info->IsBusy = obj["occupation"].toString() == "busy";
        info->Id = userId;
        info->MemberPicture = new QPixmap(QPixmap::fromImage(QImage(":/Image/Ressources/Icon/UserDefault.png")));
        _MemberAvaible->addWidget(new DashboardMember(info, this));

    }
    qDebug() << _NumberBeforeInitializingDone;
    if (_NumberBeforeInitializingDone == 0)
       UpdateLayout();
}

void BodyDashboard::GetAllProject(int id, QByteArray byte)
{
    _NumberBeforeInitializingDone--;
    QJsonDocument doc = QJsonDocument::fromJson(byte);
    QJsonObject objMain = doc.object();
    for (QJsonValueRef ref : objMain)
    {
        QJsonObject obj = ref.toObject();
        GlobalProgressInfo *currentInfo = new GlobalProgressInfo("", "", "", "", 0, 0, 0, 0);
        currentInfo->ProjectTitle = obj["project_name"].toString();
        currentInfo->ProjectMail = obj["contact_mail"].toString();
        currentInfo->MaxNumberTask = obj["number_tasks"].toInt();
        currentInfo->NumberTask = obj["number_finished_tasks"].toInt();
        currentInfo->NumberMsg = obj["number_messages"].toInt();
        currentInfo->NumberProblem = obj["number_bugs"].toInt();
        currentInfo->ProjectPicture = new QPixmap(QPixmap::fromImage(QImage(":/Image/Ressources/Icon/ProjectDefault.png")));
        _GlobalProgress->addWidget(new DashboardGlobalProgress(currentInfo, this));
        qDebug() << "Project";
    }
    qDebug() << _NumberBeforeInitializingDone;
    if (_NumberBeforeInitializingDone == 0)
       UpdateLayout();
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
