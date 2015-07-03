#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>

#include "whiteboardcanva.h"

namespace Ui {
class MainWindow;
}

class MainWindow : public QMainWindow
{
    Q_OBJECT

public:
    explicit MainWindow(QWidget *parent = 0);
    ~MainWindow();

private slots:
    void on_actionQuit_triggered();

    void on_Circle_clicked();

    void on_Rect_clicked();

    void on_Line_clicked();

    void on_eraseRadio_clicked();

private:
    Ui::MainWindow *ui;
    whiteboardcanva *whiteboard;
};

#endif // MAINWINDOW_H
