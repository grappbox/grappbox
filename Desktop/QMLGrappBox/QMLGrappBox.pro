TEMPLATE = app

QT += qml quick
CONFIG += c++11

SOURCES += main.cpp \
    CloudController.cpp \
    API/DataConnectorOnline.cpp \
    API/SDataManager.cpp \
    LoginController.cpp \
    FileDownloader.cpp \
    SDebugLog.cpp \
    GanttView.cpp \
    TaskData.cpp \
    UserData.cpp \
    GanttModel.cpp \
    DashboardModel.cpp \
    UserModel.cpp \
    TimelineModel.cpp \
    BugTrackerModel.cpp \
    ProjectSettingsModel.cpp \
    Manager/SInfoManager.cpp

RESOURCES += qml.qrc \
    images.qrc \
    modules/FontAwesome.qrc \
    modules/FontRoboto.qrc \
    modules/Icons.qrc \
    modules/Material.qrc \
    modules/MaterialQtQuick.qrc

# Additional import path used to resolve QML modules in Qt Creator's code model
QML2_IMPORT_PATH = "./modules"

# Default rules for deployment.
include(deployment.pri)

DISTFILES +=

HEADERS += \
    CloudController.h \
    API/DataConnectorOnline.h \
    API/IDataConnector.h \
    API/SDataManager.h \
    LoginController.h \
    FileDownloader.h \
    SDebugLog.h \
    GanttView.h \
    TaskData.h \
    UserData.h \
    GanttModel.h \
    ProjectData.h \
    DashboardModel.h \
    EventData.h \
    UserModel.h \
    TimelineModel.h \
    BugTrackerModel.h \
    ProjectSettingsModel.h \
    Manager/SInfoManager.h

RCC_DIR = qrc
