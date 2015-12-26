#ifndef BUGLISTTITLEWIDGET_H
#define BUGLISTTITLEWIDGET_H

#include <QWidget>
#include <QLabel>
#include <QPushButton>
#include <QHBoxLayout>

enum BugState
{
    NONE = 0,
    OPEN,
    CLOSED
};

class BugListTitleWidget : public QWidget
{
    Q_OBJECT
public:
    explicit BugListTitleWidget(QWidget *parent = 0);

signals:
    void            OnNewIssue();
    void            OnFilterStateChanged(BugState);

public slots:
    void            triggerOpenStateButtonToogled(bool);
    void            triggerClosedStateButtonToogled(bool);

private:
    QHBoxLayout     *_mainLayout;
    QPushButton     *_btnOpenState;
    QPushButton     *_btnClosedState;
    QPushButton     *_btnNewIssue;
    BugState        _filterState;
};

#endif // BUGLISTTITLEWIDGET_H
