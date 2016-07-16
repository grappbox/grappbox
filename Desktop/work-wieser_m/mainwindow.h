#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>
#include <QFileDialog>
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

public slots:
    void actionOpen();
    void actionSave();

private:
    Ui::MainWindow  *m_ui;
    Whiteboard      *m_whiteboard;
    QFileDialog     *m_fileDialog;
};

#endif // MAINWINDOW_H
