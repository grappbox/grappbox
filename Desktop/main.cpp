#include <QGuiApplication>
#include <QQmlApplicationEngine>
#include <QtQml>

#include "CloudController.h"
#include "SDebugLog.h"
#include "LoginController.h"
#include "GanttView.h"
#include "TaskData.h"
#include "UserData.h"
#include "ProjectData.h"
#include "GanttModel.h"
#include "DashboardModel.h"
#include "EventData.h"
#include "UserModel.h"
#include "TimelineModel.h"
#include "BugTrackerModel.h"
#include "ProjectSettingsModel.h"
#include "API/SDataManager.h"
#include "Manager/SInfoManager.h"
#include "Manager/DataImageProvider.h"
#include "CalendarModel.h"
#include "eventmodeldata.h"
#include "WhiteboardModel.h"

#define GRAPPBOX_URL "GrappBoxController"
#define MAJOR_VERSION 1
#define MINOR_VERSION 0

static QObject *qobject_datamanager_provider(QQmlEngine *engine, QJSEngine *scriptEngine)
{
    Q_UNUSED(engine)
    Q_UNUSED(scriptEngine)

    return API::SDataManager::GetDataManager();
}

static QObject *qobject_infomanager_provider(QQmlEngine *engine, QJSEngine *scriptEngine)
{
    Q_UNUSED(engine)
    Q_UNUSED(scriptEngine)

    return SInfoManager::GetManager();
}

static QObject *qobject_dataimageprovider_provider(QQmlEngine *engine, QJSEngine *scriptEngine)
{
    Q_UNUSED(engine)
    Q_UNUSED(scriptEngine)

    return DataImageProvider::getInstance();
}

int main(int argc, char *argv[])
{
    QGuiApplication app(argc, argv);

    LOG(QString("Initialized !"));

    qmlRegisterType<CloudController>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "CloudController");
    qmlRegisterType<FileData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "FileData");
    qmlRegisterType<FileDataTransit>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "FileDataTransit");
    qmlRegisterType<LoginController>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "LoginController");
    qmlRegisterType<GanttView>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "GanttView");
    qmlRegisterType<TaskData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "TaskData");
    qmlRegisterType<UserData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "UserData");
    qmlRegisterType<TaskTagData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "TaskTagData");
    qmlRegisterType<DependenciesData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "DependenciesData");
    qmlRegisterType<TaskRessources>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "TaskRessources");
    qmlRegisterType<GanttModel>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "GanttModel");
    qmlRegisterType<ProjectData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "ProjectData");
    qmlRegisterType<EventData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "EventData");
    qmlRegisterType<DashboardModel>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "DashboardModel");
    qmlRegisterType<UserModel>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "UserModel");
    qmlRegisterType<TimelineMessageData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "TimelineMessageData");
    qmlRegisterType<TimelineModel>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "TimelineModel");
    qmlRegisterType<BugTrackerModel>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "BugTrackerModel");
    qmlRegisterType<BugTrackerTicketData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "BugTrackerTicketData");
    qmlRegisterType<BugTrackerTags>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "BugTrackerTags");
    qmlRegisterType<RolesData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "RolesData");
    qmlRegisterType<CustomerAccessData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "CustomerAccessData");
    qmlRegisterType<ProjectSettingsModel>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "ProjectSettingsModel");
    qmlRegisterType<CalendarModel>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "CalendarModel");
    qmlRegisterType<EventModelData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "EventModelData");
    qmlRegisterType<WhiteboardData>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "WhiteboardData");
    qmlRegisterType<WhiteboardModel>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "WhiteboardModel");
    qmlRegisterSingletonType<API::SDataManager>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "SDataManager", qobject_datamanager_provider);
    qmlRegisterSingletonType<SInfoManager>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "SInfoManager", qobject_infomanager_provider);
    qmlRegisterSingletonType<DataImageProvider>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "DataImageProvider", qobject_dataimageprovider_provider);

    QQmlApplicationEngine engine;
    engine.addImportPath("modules/");
    engine.addImageProvider("api", DataImageProvider::getInstance());
    engine.load(QUrl(QStringLiteral("qrc:/qrc/qrc/main.qml")));

    int ret = app.exec();

    return ret;
}

