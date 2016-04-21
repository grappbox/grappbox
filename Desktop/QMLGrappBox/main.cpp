#include <QGuiApplication>
#include <QQmlApplicationEngine>
#include <QtQml>

#include "CloudController.h"
#include "SDebugLog.h"
#include "LoginController.h"
#include "GanttView.h"
#include "TaskData.h"
#include "UserData.h"
#include "GanttModel.h"

#define GRAPPBOX_URL "GrappBoxController"
#define MAJOR_VERSION 1
#define MINOR_VERSION 0

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
    qmlRegisterType<GanttModel>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "GanttModel");

    QQmlApplicationEngine engine;
    engine.addImportPath("modules/");
    engine.load(QUrl(QStringLiteral("qrc:/qrc/qrc/main.qml")));

    int ret = app.exec();

    return ret;
}

