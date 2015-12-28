#ifndef BUGVIEWTITLEWIDGET_H
#define BUGVIEWTITLEWIDGET_H

#include <QWidget>
#include <QLineEdit>
#include <QPushButton>
#include <QHBoxLayout>

class BugViewTitleWidget : public QWidget
{
    Q_OBJECT
public:
    explicit BugViewTitleWidget(QString title, bool creation = false, QWidget *parent = 0);
    void    SetTitle(const QString &title);
    void    SetBugID(const int bugId);
    QString GetTitle();

signals:
    void    OnIssueClosed(int);
    void    OnTitleEdit(int);

public slots:
    void    TriggerCloseIssue();
    void    TriggerEditTitle();
    void    TriggerSaveTitle();

private:
    bool            _creation;
    int             _bugID;
    QHBoxLayout     *_mainLayout;
    QPushButton     *_btnEdit;
    QPushButton     *_btnClose;
    QLineEdit       *_title;
};

#endif // BUGVIEWTITLEWIDGET_H
