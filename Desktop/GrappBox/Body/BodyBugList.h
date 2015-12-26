#ifndef BODYBUGLIST_H
#define BODYBUGLIST_H

#include <QWidget>
#include <QLabel>
#include <QPushButton>
#include <QHBoxLayout>
#include <QVBoxLayout>

class BodyBugList : public QWidget
{
    Q_OBJECT
public:
    explicit BodyBugList(QWidget *parent = 0);

signals:

public slots:

private:
    QVBoxLayout     *_mainLayout;
};

#endif // BODYBUGLIST_H
