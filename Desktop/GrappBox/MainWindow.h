#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QtWidgets/QMainWindow>
#include "ProfilMainInformation.h"

class IBodyContener;

#include <QList>

// Widget include

#include <QtWidgets/QWidget>
#include <QtWidgets/QHBoxLayout>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QGridLayout>
#include <QtWidgets/QStackedLayout>
#include <QtWidgets/QLabel>
#include <QtWidgets/QPushButton>
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

    // Profil
    ProfilMainInformation       *_ProfilWidget;
    int                         _UserSettingsId;
    int                         _SettingsId;

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
