#-------------------------------------------------
#
# Project created by QtCreator 2015-09-02T16:03:30
#
#-------------------------------------------------

QT       += core gui

greaterThan(QT_MAJOR_VERSION, 4): QT += widgets

TARGET = GrappBox
TEMPLATE = app


SOURCES += main.cpp \
    MainWindow.cpp \
    SliderMenu.cpp \
    Body\BodyDashboard.cpp \
    Body\Dashboard\DashboardMember.cpp \
    Body\Dashboard\DashboardMeeting.cpp \
    Body\Dashboard\DashboardGlobalProgress.cpp \
    SDataManager.cpp \
    Body\Dashboard\DashboardInformation.cpp \
    SFontLoader.cpp

HEADERS  += MainWindow.h \
    SliderMenu.h \
    IBodyContener.h \
    Body\BodyDashboard.h \
    Body\Dashboard\DashboardMember.h \
    Body\Dashboard\DashboardMeeting.h \
    Body\Dashboard\DashboardGlobalProgress.h \
    SDataManager.h \
    IDataConnector.h \
    Body\Dashboard\DashboardInformation.h \
    SFontLoader.h

RESOURCES += \
    temporaryressources.qrc \
    finalressources.qrc
