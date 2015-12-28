#ifndef BODYBUGLIST_H
#define BODYBUGLIST_H

#include "BugTracker/IBugPage.h"
#include "BugTracker/BugListElement.h"
#include "BugTracker/BugListTitleWidget.h"
#include <QWidget>
#include <QLabel>
#include <QPushButton>
#include <QHBoxLayout>
#include <QVBoxLayout>
#include <QScrollArea>
#include <QList>
#include <QPair>

#define UNUSED __attribute__((unused))
#define LIST_ELEM_HEIGHT    50
#define LIST_TITLE_HEIGHT   50

class BodyBugList : public QWidget, public IBugPage
{
    Q_OBJECT
public:
    explicit            BodyBugList(QWidget *parent = 0);
    virtual void        Show(BodyBugTracker *pageManager, QJsonObject *data);
    virtual void        Hide();

private:
    void                DeleteListElements();
    void                CreateList(QList<QPair<int, QString> > &elemList);

signals:
    void                OnLoadingDone(BodyBugTracker::BugTrackerPage page);

public slots:
    void                TriggerNewIssue();

private:
    BodyBugTracker      *_pageManager;
    int                 _bodyID;
    QVBoxLayout         *_mainLayout;
    QVBoxLayout         *_listAdapter;
    BugListTitleWidget  *_title;
    QScrollArea         *_listScrollView;
};

#endif // BODYBUGLIST_H
