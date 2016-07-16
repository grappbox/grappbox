#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>
#include "ProfilMainInformation.h"

class IBodyContener;

#include <QList>

// Widget include

#include <QWidget>
#include <QHBoxLayout>
#include <QVBoxLayout>
#include <QGridLayout>
#include <QStackedLayout>
#include <QLabel>
#include <QPushButton>
#include "SliderMenu.h"
#include "LoginWindow.h"

// UI include

#include <QtGui/QImage>

class MainWindow : public QMainWindow
{
    Q_OBJECT

public:
    MainWindow(QWidget *parent = 0);
    ~MainWindow();

public slots:
    void OnMenuChange(int id);
    void OnLogin();
    void OnLogout();
    void OnSettings();
    void OnUserSettings();
    void OnLoadingFinished(int);
    void OnProjectChange();

private:
    LoginWindow         *_Login;

/*
 * Private field
 * Widget
 */
private:

    // Layout
    QHBoxLayout         *_HeaderLayout;
    QVBoxLayout         *_SliderLayout;
    QHBoxLayout         *_ContainLayout;
    QVBoxLayout         *_MainLayout;

    // Canvas
    QStackedLayout      *_StackedLayout;
    int                 _CurrentCanvas;
    QLabel              *_LoadingImage;
    int                 _LoadingId;

    // Profil
    ProfilMainInformation       *_ProfilWidget;
    int                         _UserSettingsId;
    int                         _SettingsId;
    int                         _MainPageId;

    // Menu
    SliderMenu          *_MenuWidget;

    // HeaderBar
    QLabel              *_GrabboxNameLabel;
    QPushButton         *_NotificationButton;
    QPushButton         *_AlertButton;
    QPushButton         *_ParameterButton;
};

#include "ibodycontener.h"

#endif // MAINWINDOW_H
