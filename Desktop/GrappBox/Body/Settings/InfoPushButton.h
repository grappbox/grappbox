#ifndef INFOPUSHBUTTON_H
#define INFOPUSHBUTTON_H

#include <QPushButton>
#include <QWidget>

class InfoPushButton : public QPushButton
{
    Q_OBJECT
public:
    InfoPushButton(QWidget *parent = 0);
    void    SetInfo(int info);
    int     GetInfo();

public slots:
    void ReleaseSlot();

signals:
    void ReleaseInfo(int);

private:
    int _info;

};

#endif // INFOPUSHBUTTON_H
