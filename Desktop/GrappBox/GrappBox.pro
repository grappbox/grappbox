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
    BodyDashboard.cpp \
    DashboardMember.cpp \
    DashboardMeeting.cpp \
    DashboardGlobalProgress.cpp \
    SDataManager.cpp

HEADERS  += MainWindow.h \
    SliderMenu.h \
    IBodyContener.h \
    BodyDashboard.h \
    DashboardMember.h \
    DashboardMeeting.h \
    DashboardGlobalProgress.h \
    SDataManager.h \
    IDataConnector.h
