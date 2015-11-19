#ifndef WHITEBOARDBUTTONCHOICE_H
#define WHITEBOARDBUTTONCHOICE_H

#include <QWidget>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QLabel>
#include <QPushButton>

class BodyDashboard;

class WhiteboardButtonChoice : public QWidget
{
private:
    Q_OBJECT
public:
    WhiteboardButtonChoice(int whiteboardId, BodyDashboard *mainApp, QWidget *parent = NULL);

signals:
    void OnEdit(int);

public slots:
    void Edit();

private:
    QVBoxLayout     *_MainLayout;
    QPushButton     *_EditButton;
    QLabel          *_NameWhiteboard;
    QLabel          *_EditTime;
    QLabel          *_EditDate;

    int             _WhiteboardID;
};

#include "BodyDashboard.h"

#endif // WHITEBOARDBUTTONCHOICE_H
