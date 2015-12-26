#-------------------------------------------------
#
# Project created by QtCreator 2015-09-02T16:03:30
#
#-------------------------------------------------

QT       += core gui widgets network

TARGET = GrappBox
TEMPLATE = app

QMAKE_CXXFLAGS += -Wunused-parameter \
    -Wunused-variable

CONFIG += c++11

INCLUDEPATH += Body \
    Body/Dashboard \
    API

SOURCES += main.cpp \
    MainWindow.cpp \
    SliderMenu.cpp \
    Body\BodyDashboard.cpp \
    Body\Dashboard\DashboardMember.cpp \
    Body\Dashboard\DashboardMeeting.cpp \
    Body\Dashboard\DashboardGlobalProgress.cpp \
    Body\Dashboard\DashboardInformation.cpp \
    SFontLoader.cpp \
    ProfilMainInformation.cpp \
    BodyWhiteboard.cpp \
    whiteboardcanvas.cpp \
    WhiteboardButtonChoice.cpp \
    WhiteboardGraphicsView.cpp \
    customgraphicsdiamonditem.cpp \
    CustomGraphicsHandWriteItem.cpp \
    BodyWhiteboardWritingText.cpp \
    API/SDataManager.cpp \
    API/DataConnectorOnline.cpp \
    LoginWindow.cpp \
    Body/BodyUserSettings.cpp \
    Body/BodyProjectSettings.cpp \
    Body/Settings/ImageUploadWidget.cpp \
    Body/Settings/RoleTableWidget.cpp \
    Body/Settings/UserRoleCheckbox.cpp \
    Body/Settings/CreateNewRole.cpp \
    Body/Settings/InfoPushButton.cpp \
    Body/Settings/InviteUserWindow.cpp \
    Body/Settings/CustomerAccessSettings.cpp \
    Body/Settings/CreateNewCustomerAccessWindow.cpp \
    Body/BodyBugList.cpp \
    Body/BugTracker/BugListTitleWidget.cpp \
    Body/BugTracker/BugListElement.cpp

HEADERS  += MainWindow.h \
    SliderMenu.h \
    IBodyContener.h \
    Body\BodyDashboard.h \
    Body\Dashboard\DashboardMember.h \
    Body\Dashboard\DashboardMeeting.h \
    Body\Dashboard\DashboardGlobalProgress.h \
    Body\Dashboard\DashboardInformation.h \
    SFontLoader.h \
    ProfilMainInformation.h \
    BodyWhiteboard.h \
    whiteboardcanvas.h \
    WhiteboardButtonChoice.h \
    WhiteboardGraphicsView.h \
    customgraphicsdiamonditem.h \
    CustomGraphicsHandWriteItem.h \
    BodyWhiteboardWritingText.h \
    API/IDataConnector.h \
    API/SDataManager.h \
    API/DataConnectorOnline.h \
    LoginWindow.h \
    Body/BodyUserSettings.h \
    Body/BodyProjectSettings.h \
    Body/Settings/ImageUploadWidget.h \
    Body/Settings/RoleTableWidget.h \
    Body/Settings/UserRoleCheckbox.h \
    Body/Settings/CreateNewRole.h \
    Body/Settings/InfoPushButton.h \
    Body/Settings/InviteUserWindow.h \
    Body/Settings/CustomerAccessSettings.h \
    Body/Settings/CreateNewCustomerAccessWindow.h \
    Body/BodyBugList.h \
    Body/BugTracker/BugListTitleWidget.h \
    Body/BugTracker/BugListElement.h

RESOURCES += \
    temporaryressources.qrc \
    finalressources.qrc

DISTFILES +=
