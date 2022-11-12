package main

import (
	"bufio"
	"encoding/binary"
	"fmt"
	"log"
	"net"
	"strings"
)

func main() {
	log.Print("Start")

	// Устанавливаем прослушивание порта
	ln, err := net.Listen("tcp", ":8081")
	if err != nil {
		log.Fatalln(err)
	}
	defer ln.Close()
	log.Print("Start listening")

	var cnt uint64 = 0
	for {
		conn, err := ln.Accept()
		if err != nil {
			log.Print(err)
			continue
		}
		cnt++
		go handleConnection(conn, cnt)
	}
}

func handleConnection(c net.Conn, cnt uint64) {
	log.Printf("[%d] new conection %s <-> %s", cnt, c.LocalAddr().String(), c.RemoteAddr().String())
	defer c.Close()
	err := writeString(c, fmt.Sprintf("Greeting %d", cnt))
	if err != nil {
		log.Printf("[%d] err on greeting: %v", cnt, err)
		return
	}

	for {
		netData, err := bufio.NewReader(c).ReadString('\n')
		if err != nil {
			log.Printf("[%d] err: %v", cnt, err)
			break
		}
		log.Printf("[%d] %s", cnt, netData)

		temp := strings.TrimSpace(string(netData))
		if temp == "STOP" {
			break
		}
	}

	err = writeString(c, fmt.Sprintf("Goodbye %d", cnt))
	if err != nil {
		log.Printf("[%d] err on goodbyeing: %v", cnt, err)
	}

	log.Printf("[%d] close", cnt)

}

func writeString(c net.Conn, s string) error {

	b := []byte(s)
	bs := make([]byte, 4)
	binary.BigEndian.PutUint32(bs, uint32(len(b)))

	w := bufio.NewWriter(c)
	_, err := w.Write(bs)
	if err != nil {
		return err
	}
	_, err = w.Write(b)
	if err != nil {
		return err
	}
	err = w.Flush()
	if err != nil {
		return err
	}

	return nil
}
