#ifndef BUGVIEWTITLEWIDGET_H
#define BUGVIEWTITLEWIDGET_H

#include <QWidget>
#include <QLabel>
#include <QPushButton>
#include <QHBoxLayout>

class BugViewTitleWidget : public QWidget
{
    Q_OBJECT
public:
    explicit BugViewTitleWidget(int bugId, QString title, QWidget *parent = 0);

signals:
    void    OnIssueClosed(int);
    void    OnIssueEdit(int);

public slots:
    void    TriggerCloseIssue();

private:
    int             _bugID;
    QHBoxLayout     *_mainLayout;
    QPushButton     *_btnEdit;
    QPushButton     *_btnClose;
};

#endif // BUGVIEWTITLEWIDGET_H
