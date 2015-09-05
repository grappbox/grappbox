#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>

// Widget include

#include <QWidget>
#include <QtWidgets/QHBoxLayout>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QGridLayout>
#include <QtWidgets/QLabel>
#include <QtWidgets/QPushButton>
#include "SliderMenu.h"

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
    QPushButton         *_CurrentCanvas; // Temporary, will be replaced by the real Canvas system

    // Profil
    QWidget             *_ProfilWidget;

    // Menu
    SliderMenu          *_MenuWidget;

    // HeaderBar
    QLabel              *_GrabboxNameLabel;
    QPushButton         *_NotificationButton;
    QPushButton         *_AlertButton;
    QPushButton         *_ParameterButton;
};

#endif // MAINWINDOW_H
