#ifndef BUGLISTELEMENT_H
#define BUGLISTELEMENT_H

#include "BodyBugTracker.h"
#include <QWidget>
#include <QLabel>
#include <QHBoxLayout>
#include <QPushButton>
#include <QJsonObject>
#include <QFile>
#include <QDebug>

class BugListElement : public QWidget
{
    Q_OBJECT
public:
    explicit BugListElement(BodyBugTracker *pageManager, const QString &bugTitle, const int bugId, QWidget *parent = 0);

signals:
    void            OnCloseBug(int);

public slots:
    void            TriggerBtnView();
    void            TriggerBtnClose();

private:
    BodyBugTracker  *_pageManager;
    int             _bugID;
    QLabel          *_title;
    QHBoxLayout     *_mainLayout;
    QPushButton     *_btnViewBug;
    QPushButton     *_btnCloseBug;
    static bool     _pair;
};

#endif // BUGLISTELEMENT_H
