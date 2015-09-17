#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>

class IBodyContener;

#include <QList>

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
    QList<IBodyContener*>       _Canvas;
    int                         _CurrentCanvas;

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

#include "ibodycontener.h"

#endif // MAINWINDOW_H
