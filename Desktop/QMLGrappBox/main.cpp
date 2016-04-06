#include <QGuiApplication>
#include <QQmlApplicationEngine>
#include <QtQml>

#include "CloudController.h"
#include "SDebugLog.h"
#include "LoginController.h"
#include "GanttArrow.h"

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
    qmlRegisterType<GanttArrow>(GRAPPBOX_URL, MAJOR_VERSION, MINOR_VERSION, "GanttArrow");

    QQmlApplicationEngine engine;
    engine.addImportPath("modules/");
    engine.load(QUrl(QStringLiteral("qrc:/qrc/qrc/main.qml")));

    int ret = app.exec();

    return ret;
}

