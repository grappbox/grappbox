#ifndef BUGVIEWPREVIEWWIDGET_H
#define BUGVIEWPREVIEWWIDGET_H

#include "SDataManager.h"
#include <QWidget>
#include <QScrollArea>
#include <QLabel>
#include <QTextEdit>
#include <QPixmap>
#include <QPushButton>
#include <QHBoxLayout>
#include <QVBoxLayout>
#include <QString>
#include <QDateTime>
#include <QLineEdit>

#define PH_BUGPREVIEWDATE tr("Commented the")

class BugViewPreviewWidget : public QWidget
{
    Q_OBJECT
public:
    explicit BugViewPreviewWidget(bool isCreation = false, bool createPage = false, QWidget *parent = 0);
    void SetDate(const QDateTime &date);
    void SetCommentor(const QString &name);
    void SetID(const int id);
    void SetAvatar(const QPixmap &avatar);
    void SetComment(const QString &comment);
    void SetCommentTitle(const QString &title);
    const QString &GetComment() const;
    const QString &GetCommentTitle() const;

signals:
    void        OnEdit(int);
    void        OnSaved(int);
    void        OnCommented();

public slots:
    void        TriggerEditBtnReleased();
    void        TriggerCommentBtnReleased();

private:
    QString     FormatDateTime(const QDateTime &datetime);

private:
    int         _bugID;
    QVBoxLayout *_mainLayout;
    QHBoxLayout *_titleBar;
    QHBoxLayout *_statusBar;
    QLabel      *_avatar;
    QPushButton *_btnEdit;
    QPushButton *_btnComment;
    QLabel      *_lblName;
    QLabel      *_lblDate;
    QTextEdit   *_comment;
    QLineEdit   *_commentTitle;
};

#endif // BUGVIEWPREVIEWWIDGET_H
