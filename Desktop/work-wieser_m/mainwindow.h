#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>
#include "whiteboard.h"

namespace Ui {
class MainWindow;
}

class MainWindow : public QMainWindow
{
    Q_OBJECT

public:
    explicit MainWindow(QWidget *parent = 0);
    ~MainWindow();

private:
    Ui::MainWindow  *m_ui;
    Whiteboard      *m_whiteboard;

};

#endif // MAINWINDOW_H
